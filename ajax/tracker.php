<?php
//file to handle the actual tracking of time
//this will be called by a ajax call on all pages

include "../php/functions.php";
include "../connect.ini";
session_start();




//it will show either the current job with a stop button
//or a list of jobs and a start button
  //see if user is currently working
  $sql = "SELECT entries.id, categories_id, display_name, start_time, end_time, comment, tags, project_id 
  FROM entries
  LEFT JOIN categories
  ON entries.categories_id = categories.id 
  WHERE end_time IS NULL Limit 1;";
  $result = $conn->query($sql);
  logAction("Ran SQL on DB, " . $sql, "file");
  $row = mysqli_fetch_array($result);


if ($row) {
  //if you are currently working this will happen
  echo "<div class='current-job' style=display:none; >
      <div>
        <span onclick=toggleCurrentJobView()>Shrink</span>
      </div>
      <input type='hidden' id='entryId' value='" . $row['id'] . "'>
      <h2>Your Current Open Job</h2>
      <p><label for='curEntryCat'>Current Category:</label>
          " . CategoryDropList('curEntries', 'N', $row['categories_id']) . "</p>
      <p><strong>Time Open:</strong> <span id='timer'></span>
      <input type='hidden' id='timerValue' value='" . openJob() . "'>
      </p>
      <form method='GET' action='stop_work.php'>
        <div class='form-group'>
          <label for='comment'>Comment:</label>
          <textarea name='comment' id='comment' rows='4' cols='50'>" . $row['comment'] . "</textarea>
        </div>
        <div class='form-group'>
          <label for='tags'>Project:</label>
          " . generateProjectsList('project_id', $row['project_id']) . "
        </div>

        <div class='form-group tags'>
          <label for='addTags'>Add Tags:</label>
          <input type='text' name='addTags' id='addTags' onkeyup='showTags(this.value)'>
          <div id='divTags'></div>
          <input type='hidden' name='tags' id='tags' value='" . $row['tags'] . "'>
          <div id='displayTags'></div>
        </div>
        <br>
        <div class='form-group'>
          <input type='button' value='Save' onclick='saveEntry()'>
        </div>
        <hr>
        <div class='form-group'>
          <label for='category'>Category:</label>
          " . CategoryDropList('entries', 'N') . "
        </div>
        <div class='form-group'>
          <label for='interrupted'>Were you interrupted:</label>
          <input name='interrupted' id='interrupted' type='checkbox' value='Y'>
        </div>
        <div class='form-group'>
          <input type='button' value='Stop Work' onclick=stopTimer()>
        </div>
      </form>
      </div>

      <div class='current-job-mini'>
        <div>
          <span onclick=toggleCurrentJobView()>Expand</span>
        </div>
        <div>  
          <p><strong>Working</strong></p>
        </div>
        <div>
          <p> " . $row['display_name'] . "</p>
        </div>
        <div>
          <p><span id='timer2'></span></p>
        </div>
      </div>
      ";


} else {
  //if you are not currently working on anything this will happen
  echo "<div class='no-current-job current-job'>
      <div>
        <span onclick=toggleCurrentJobView()>Shrink</span>
      </div>
    <h2>No Current Open Job</h2>
    <p>Would you like to open a new job?</p>
    <p>Current time: <span id='timer'></span>
    <input type='hidden' id='timerValue' value='" . openJob() . "'>
    </p>
    <form method='GET' action='start_work.php'>
      <div class='form-group'>
      <label for='category'>Category:</label>
      " . CategoryDropList('entries', 'N') . "
      </div>
      <div class='form-group'>
      <label for='comment'>Comment:</label>
      <textarea name='comment' id='comment' rows='4' cols='50'></textarea>
      </div>
      <div class='form-group'>
       <div id='linkToProject'></div>
      </div>
      <div class='form-group'>
      <input type='button' value='Get To Work' onclick=startTimer()>
      </div>
    </form>
    </div>
          <div class='current-job-mini' style='display:none;'>
        <div>
          <span onclick=toggleCurrentJobView()>Expand</span>
        </div>
        <div>  
          <p><strong>Not currently working</strong></p>
        </div>
        <div>
          <p><span id='timer2'></span></p>
        </div>
      </div>
    ";
}
