**Make code quality your priority. We plan to support this project for some time so debuggability, maintainability and extensibility are important**

### Process
- Make sure you have last version of the code and database. Before working on something new make sure you do not have uncommitted changes and run
```
git checkout main && git pull --rebase
``` 
- Update dependencies and database
```
symfony composer install
./bin/reset-dev.sh
``` 
- Find your next tasks on https://etepia.atlassian.net/jira/software/projects/GROSH/boards/4. Tasks are ordered by priority
- Read the task, make sure you understand what is required, fill out "Original estimate" to the task
- Move task to In Progress column
- Create a new branch for the ticket, it should start from the ticket key, eg "GROSH-1-add-default-values" or you "Create branch" link in the ticket
```
git checkout -b GROSH-1-add-default-values
```
- **Test results of your work. There is no QA**
- Fix all code style issues if you made any php changes. Ping me if you've used a better schema in the past, and we can improve here
```
php ./vendor/bin/ecs --fix
```
- Before commit your code, please make sure all rules from eslint is passed. 
- Fix the javascript and vue code to follow our code style guidelines
```
npm run format
npm run format:fix
```
- Fix all Javascript to ensure consistent code formatting if you made any Javascript changes.
```
npm run lint
npm run lint:fix
```
- Please ensure that the code passes all unit tests
```
npm run jest
```
- Ping me if you've used a better schema in the past, and we can improve here


- Commit you code. Every timeframe longer than 2 hours should have at least one commit. But do not commit every 10 min, it creates unnecessary noise
- If you are 1.5x from original estimation, commit your current state, STOP your time tracker and send me a message.
- Push your code to the server
```
git push -u origin GROSH-1-add-default-values
```
- Create PR, include ticket number in the PR. All functionality should be tested before creating the pull request
- Move ticket to the "In Review" state, do not change ticket owner.
- Add time spent to the ticket (click on Time Tracking field)
- Ideally it should only be 1-2 tickets in the "IN PROGRESS" state unless there is a serious blocker
- I will review PR, approve it, merge the PR and move ticket to the "DONE" state
  -- Usually PRs have few comments and do not require long back and fourth
  -- For all approved but not merged PRs please address all additional comments and merge
  -- All "nit" comments are optional, use your judgement to define we need to implement them

#### Refactoring
- Do not hesitate make small changes in the code around your part
- If you see big issues with the existing approach or code, please raise your concerns