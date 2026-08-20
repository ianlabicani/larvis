# Contributing to Larvis

Larvis uses one issue per branch so changes remain focused, reviewable, and easy to trace.

## Start with an issue

Choose the feature, bug, or UI/UX form in GitHub's **New issue** flow. Define the intended outcome, acceptance criteria, and practical test scenarios before implementation begins. Blank issues remain available when none of the structured forms fit.

## Create a dedicated branch

Every issue branch must use the latest `main` as its base. Do not base a new issue branch on another issue or feature branch. Use this format:

```text
codex/<number>-<short-description>
```

For example, issue 7 uses `codex/7-github-workflow`. Keep unrelated work on separate branches unless broader scope has been explicitly approved.

## Test the change

Add or update automated tests for changed behavior. Run the relevant repository checks and include practical verification steps so a reviewer can confirm the result. Do not rely only on a description of the implementation.

## Open and merge the pull request

Open a pull request against `main`. Complete the pull-request template and include `Closes #<number>` in the body so GitHub closes the issue when the pull request merges.

After review and all required checks pass, prefer a rebase merge to keep the history linear. Delete the completed branch after it is merged.
