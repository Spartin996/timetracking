[y]Need to move the time spent calculation into a function so I can add 1 to 0 minutes

[y] actually get the comments from the open entry

[y] add the start time to the open job message

[y]change the search SQL to be start time during period

[y]add a all categories page and a edit categories page
  [] If active, show a tick; if not, show an X
  []change the seq from a text field to a dropdown or slider for better usability

[y]add a entries page with filters

[] add a close button to the entry forms just to make it look nicer

[y] change yesterday to fortnightly on the index.php

[] add a delete option to the tables
  This will need to check if the categories are in use anywhere so perhaps not

[y] when entering the data into any of the insert statements it will repopulate the field as a new entry with the same data. this means that you can submit the same form and create duplicates

[] try a better datetime input, firefox does not have a now button (SILLY).

[y] add order by to the reports.

[y] check all the html that I have quotes around attributes

[y] add total time worked to summary function

[y] update js to fix leading 0's

[y] add a ongoing job to reports

[y] can we add the job open counter to the ongoing in the table?

[] The JS minutesSince function cannot count past 1 hour; add an hours option.

[y] make more obvious that it is running
  change the main header color slightly to reflect active

[X] perhaps add a pause to ongoing tasks after a set number of hours
  disregard as you will need to correct it manually so this makes it clear that it is required

[y] change config.php to a gitnore or raise up a level further

[y] add a interrupted flag to the DB and button to add it
  [y] cleanup the stop.php file
  [] need to add a config page to check if the 'interrupted' field is in the db and update it if missing.


[y] need to add a form to add tags to the list of supported tags

[y] need to use the tags table to generate the JS list of tags

[y] need to restructure the files into folders

[y] add a catid of 0 to call category filters to support creating a project or entry without a category.



**cleanup before making public**

[] update the entries popup to work on 90% of pages

[] error catching and installation help

