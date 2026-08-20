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

## Inspect the database schema

[Laravel Truss](https://trussphp.com) is available in local development when Composer development dependencies are installed. Start the application and open `/truss` to inspect its tables, columns, indexes, and foreign-key relationships as an interactive diagram.

Truss reads database structure only; it does not query or display application row data. It is disabled outside the local environment, is absent from production installations created with `composer install --no-dev`, and its optional MCP server is intentionally disabled in Larvis.

Use the bundled commands for terminal inspection and deterministic exports:

```shell
php artisan truss:show
php artisan truss:export --format=dbml
php artisan truss:doctor
```

Run `php artisan help truss:export` or `php artisan help truss:doctor` for filtering, output formats, and validation options.

## Open and merge the pull request

Open a pull request against `main`. Complete the pull-request template and include `Closes #<number>` in the body so GitHub closes the issue when the pull request merges.

After review and all required checks pass, prefer a rebase merge to keep the history linear. Delete the completed branch after it is merged.
