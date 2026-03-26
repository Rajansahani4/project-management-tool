Review a GitHub pull request by number. Usage: /review-pr <PR_NUMBER>

Steps:
1. Fetch the PR details with: `gh pr view $ARGUMENTS --json number,title,body,headRefName,baseRefName,state,files`
2. Fetch the full diff with: `gh pr diff $ARGUMENTS`
3. Analyze the diff thoroughly against the project's architecture standards (CLAUDE.md)
4. Produce a structured review with the following sections:

## PR #<number> — <title>

### Summary
Brief description of what this PR does.

### Verdict
One of: **Approve** / **Request Changes** / **Needs Discussion**

### Issues Found
List any bugs, violations of CLAUDE.md standards, type mismatches, missing error handling, broken contracts, etc. Use severity labels:
- 🔴 **Critical** — breaks functionality or violates hard architecture rules
- 🟡 **Major** — significant quality or correctness concern
- 🟠 **Minor** — style, naming, or small logic concern
- 🔵 **Suggestion** — optional improvement

For each issue include: file path + line reference, description, and suggested fix.

### Positives
What was done well (don't skip this — good patterns deserve reinforcement).

### Test Coverage
Are the changes testable? Are tests included or should they be?

Keep the review factual, specific, and actionable.
