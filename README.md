# Time Tracking

This is a lite utility to track time spent on a defined list of categories with a simple todo list and reporting.

It is structured using a PHP server with MYSQL.
Something along the lines of XAMPP for Windows.

**Install**

  1. Download and install XAMPP, Laragon, or a similar LAMP/WAMP stack.
  2. Place the Time Tracking application folder in the web server root directory (e.g., `htdocs` or `www`).
  3. Open the app in your browser (e.g., `http://localhost/timetracking`). You will be redirected to the installer.
  4. Enter your MySQL host, username, password, database name, and timezone, then click Install.
  5. The installer creates `config.php`, creates the database if needed, and applies the schema. You are then sent to the app.

**Manual config (optional)**

  Copy `Install/config.php.example` to `config.php` in the application root and edit the credentials. On the next page load the database will be created (if missing) and any pending SQL files under `sql/` will be applied.

**Keeping the schema up to date**

  Add new migration files under `sql/` using the naming pattern `V2.3_description.sql`. The next request applies any files not yet recorded in the `schema_migrations` table.


**Database structure**

### entries - The main time tracking table containing
- Primary key (`id`)
- Foreign key to categories (`categories_id`)
- Time tracking fields (`start_time`, `end_time`, `minutes`)
- Status flags (`interrupted` - Y/N flag for interrupted sessions)
- Metadata (`comment`, `tags`, `last_modified`)
- Project association (`project_id` - varchar(4), can be null)

### categories - Work categories/types with
- Basic info (`id`, `display_name`, `description`)
- Status and ordering (`active`, `seq`)
projects - Project management table featuring:
- Project metadata (`title`, `project_desc`, `date_created`, `date_closed`)
- Category association (`project_cat`)
- Progress tracking (`minutes`, `steps`, `steps_complete`, `steps_incomplete`)
- Progress tracking (`minutes`, `steps`, `steps_complete`, `steps_incomplete`)

### tags - Simple tag system

### settings - Application configuration
- Stores key-value pairs for app-wide settings
- Example fields: (`id`, `setting_key`, `setting_value`, `description`)
