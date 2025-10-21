# Time Tracking

This is a lite utility to track time spent on a defined list of categories with a simple todo list and reporting.

It is structured using a PHP server with MYSQL.
Something along the lines of XAMPP for Windows.

**Install**

  1. Download and install XAMPP or a similar LAMP/WAMP stack.
  2. Place the Time Tracking application files in the web server root directory (e.g., `htdocs` for XAMPP).
  3. Configure the database connection settings in `config.php`.
  4. Create a MySQL database and import the provided SQL schema.
  5. Access the application in your browser (e.g., `http://localhost/timetracking`).


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