# Approve Code WebApp
GitHub app for review PR and set approve/reject status via GH statuses

## Installation
#### 1. Install dependencies:
`composer install`

#### 2. Install bower dependencies:
`bower install`

#### 3. Configure
[Create](https://github.com/settings/applications/new) github application and set ***Authorization callback URL***
with `http://127.0.0.1:8000/login/check-github`.

Set `client_id` and `client_secret` in `app/config/hwi_oauth.yml`

#### 4. Create database structure
Execute `app/console doctrine:schema:update --force` to create database structure.

#### 5. Start application
`app/console server:run`

#### Open `http://127.0.0.1:8000` in browser

Enjoy!
