# User Database Web App

This project is a small web application that lets users submit a name and age, stores the data in a MySQL database, and displays the stored records in a table on the same page.

## Files

- `user.html`
  - The frontend page where the user enters `name` and `age`.
  - Uses a dark theme and centered layout.
  - Sends form data to `in.php` using AJAX so the page stays open and the table updates live.
  - Contains JavaScript to:
    - load table data from `in.php` using `fetch` and JSON
    - render the table rows in the page
    - submit new users without page reload
    - clear input fields after successful submit
    - display a temporary status message
    - send toggle requests to update a user status

- `in.php`
  - The backend endpoint that connects to the MySQL database.
  - Handles both data reads and writes through query parameters.
  - Supports AJAX requests by returning JSON when `?ajax=1` is present.
  - Handles actions:
    - insert a new row when `name` and `age` are provided
    - toggle `status` between `0` and `1` when `toggle_id` is provided
    - return the current rows ordered by `id` ascending
  - Uses prepared statements for insert, duplicate-check, and toggle operations.

## How it works

### Frontend (`user.html`)

1. On load, JavaScript calls `in.php?ajax=1` to fetch all saved users.
2. The table is built dynamically using the returned JSON rows.
3. The form submits using JavaScript `fetch` instead of regular form submission.
4. After submitting, the page does not reload.
5. If the insert succeeds, the form fields are cleared and the table refreshes.
6. Each row includes a `Toggle` button that sends a request to `in.php` to flip the row's `status` value.

### Backend (`in.php`)

1. Connects to MySQL using the configured host, username, password, and database.
2. Reads `toggle_id`, `name`, and `age` from `$_GET`.
3. If `toggle_id` is set, it executes:
   ```php
   UPDATE user SET status = 1 - status WHERE id = ?
   ```
   This flips the status between `1` and `0`.
4. If `name` and `age` are provided, it checks for duplicates first:
   ```php
   SELECT id FROM user WHERE name = ? AND age = ?
   ```
5. If the user does not already exist, it inserts a new record:
   ```php
   INSERT INTO user (name, age, status) VALUES (?, ?, 0)
   ```
6. When handling an AJAX request, the script returns JSON with a `message` and `rows`.

## Database table structure

Your MySQL table should look like this:

```sql
CREATE TABLE user (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  age INT NOT NULL,
  status TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id)
);
```

## Setup instructions

1. Put both `user.html` and `in.php` in the same folder on your web server.
2. Make sure your web server supports PHP.
3. Update the database credentials in `in.php`.
4. Create the `user` table in MySQL.
5. Open `user.html` in the browser.

## Notes

- The page currently uses `GET` parameters for simplicity.
- The form submission and toggle operations are done via AJAX to keep the UI on the same page.
- The table is always visible, and empty state is indicated by a `No records found.` row.
- The app uses a dark theme with teal and gold accents.
