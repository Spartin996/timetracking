# Time Tracking

This is a lite utility to track time spent on a defined list of categories.

It is structured using a PHP server with MYSQL.
  Something along the lines of Xampp for windows.

**Install**



**structure**
*pages*
Home page - landing page
    allows for creating of _entries_ so start a job finish a job stuff - perhaps include a timer perhaps you have been going for
    allow for ending of job _need to allow for leaving page_
    perhaps a summary of the last 10 jobs

edit page - allow for manually editing entries

report page - allow for generating reports for a period 
  for example work done for 14 days
   total time spend on each catergory
   total time spent on a single cat between dates.
  export to CSV??


*database*
Have a database to store all user controlled data
have 2 tables
  categories - holds job categories
    Fields
      id
      display_name - used in all dropdowns
      desc - possibly used in longer descriptions fields
      active - used to move things to the bottom of the list
      seq - to define order shown

  Entries
    Fields
      id
      cat_id - foreign key to categories table
      start_time - date_timefield to allow for times going past midnight working in 24 hr time
      end_time
      comment - just a varchar to allow for notes on work
      edited - this just updates if you edit it after the fact


