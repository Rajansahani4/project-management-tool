import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import axios from 'axios'

// ─── Helpers ────────────────────────────────────────────────────────────────

function makeAxiosError(status, data, url = '/some/endpoint') {
  const error = new Error(`Request failed with status ${status}`)
  error.response = { status, data }
  error.config   = { url }
  return error
}

// ─── Tests ───────────────────────────────────────────────────────────────────

describe('api/client — request interceptor', () => {
  afterEach(() => {
    localStorage.clear()
    vi.restoreAllMocks()
  })

  async function captureRequestHeaders(token) {
    if (token) {
      localStorage.setItem('auth_token', token)
    } else {
      localStorage.removeItem('auth_token')
    }

    vi.resetModules()
    const { default: client } = await import('@/api/client.js')

    let capturedHeaders = null
    // Install a minimal adapter that records the final request config
    client.defaults.adapter = (config) => {
      capturedHeaders = config.headers
      return Promise.resolve({
        data:       {},
        status:     200,
        statusText: 'OK',
        headers:    {},
        config,
        request:    {},
      })
    }

    await client.get('/test').catch(() => {})
    return capturedHeaders
  }

  it('attaches Authorization header when token exists in localStorage', async () => {
    const headers = await captureRequestHeaders('my-jwt')
    // Authorization may be an AxiosHeaders instance or plain object
    const auth = headers?.Authorization ?? headers?.authorization
    expect(auth).toBe('Bearer my-jwt')
  })

  it('does NOT attach Authorization header when no token', async () => {
    const headers = await captureRequestHeaders(null)
    const auth = headers?.Authorization ?? headers?.authorization
    expect(auth).toBeUndefined()
  })
})

describe('api/client — response interceptor', () => {
  afterEach(() => {
    localStorage.clear()
    vi.restoreAllMocks()
  })

  it('removes auth_token and dispatches auth:expired on 401 outside auth endpoints', async () => {
    localStorage.setItem('auth_token', 'old-token')

    vi.resetModules()
    const { default: client } = await import('@/api/client.js')

    const dispatchSpy = vi.spyOn(window, 'dispatchEvent')

    const interceptors = client.interceptors.response
    const errorHandler = interceptors.handlers[0]?.rejected
    if (!errorHandler) return

    await errorHandler(makeAxiosError(401, { message: 'Unauthenticated.' }, '/projects')).catch(() => {})

    expect(localStorage.getItem('auth_token')).toBeNull()
    expect(dispatchSpy).toHaveBeenCalledWith(expect.objectContaining({ type: 'auth:expired' }))
  })

  it('does NOT clear token on 401 from /auth/login', async () => {
    localStorage.setItem('auth_token', 'tok')

    vi.resetModules()
    const { default: client } = await import('@/api/client.js')

    const interceptors = client.interceptors.response
    const errorHandler = interceptors.handlers[0]?.rejected
    if (!errorHandler) return

    await errorHandler(makeAxiosError(401, { message: 'Invalid credentials' }, '/auth/login')).catch(() => {})

    expect(localStorage.getItem('auth_token')).toBe('tok')
  })

  it('normalises error to { message, errors, status }', async () => {
    vi.resetModules()
    const { default: client } = await import('@/api/client.js')

    const interceptors = client.interceptors.response
    const errorHandler = interceptors.handlers[0]?.rejected
    if (!errorHandler) return

    const raw = makeAxiosError(422, {
      message: 'Validation failed',
      errors: { email: ['taken'] },
    })

    await errorHandler(raw).catch((normalised) => {
      expect(normalised).toEqual({ message: 'Validation failed', errors: { email: ['taken'] }, status: 422 })
    })
  })

  it('falls back gracefully when response is absent (network error)', async () => {
    vi.resetModules()
    const { default: client } = await import('@/api/client.js')

    const interceptors = client.interceptors.response
    const errorHandler = interceptors.handlers[0]?.rejected
    if (!errorHandler) return

    const networkError = new Error('Network Error')
    // no error.response

    await errorHandler(networkError).catch((normalised) => {
      expect(normalised.status).toBe(0)
      expect(normalised.message).toBe('Network Error')
      expect(normalised.errors).toEqual({})
    })
  })
})
