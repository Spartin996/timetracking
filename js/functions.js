// functions in js
// V1.1 Created 2024-01-06 By MM - First version

var possibleTags = [];

const compactQuillToolbar = [
  [{ list: "bullet" }, { list: "ordered" }],
  ["bold", "underline"],
  ["link"],
  ["clean"],
];

// Initialize compact Quill editors inside a root node (default: document).
function initQuillEditors(root) {
  root = root || document;
  if (typeof Quill === "undefined") {
    return;
  }

  root.querySelectorAll(".quill-editor:not([data-quill-ready])").forEach((el) => {
    const quill = new Quill(el, {
      modules: {
        toolbar: compactQuillToolbar,
      },
      theme: "snow",
      placeholder: "Add a comment…",
    });
    el.setAttribute("data-quill-ready", "1");

    const sync = function () {
      const targetName = el.getAttribute("data-quill-target");
      if (!targetName) {
        return;
      }
      const hidden =
        (el.parentElement && el.parentElement.querySelector("#" + targetName)) ||
        document.getElementById(targetName);
      if (hidden) {
        hidden.value = quill.getSemanticHTML();
      }
    };

    sync();
    quill.on("text-change", sync);
  });
}

// Push current Quill HTML into hidden inputs before submit / AJAX.
function syncQuillInputs(root) {
  root = root || document;
  if (typeof Quill === "undefined") {
    return;
  }

  const scope = root.querySelectorAll ? root : document;
  scope.querySelectorAll(".quill-editor[data-quill-ready]").forEach((el) => {
    const quill = Quill.find(el);
    const targetName = el.getAttribute("data-quill-target");
    if (!quill || !targetName) {
      return;
    }
    const hidden =
      (el.parentElement && el.parentElement.querySelector("#" + targetName)) ||
      document.getElementById(targetName);
    if (hidden) {
      hidden.value = quill.getSemanticHTML();
    }
  });
}

function minutesSince(startTime) {
  let currentDate = new Date();
  if (startTime === "No Open Job") {
    let hours = currentDate.getHours();
    let minutes = currentDate.getMinutes();
    let seconds = currentDate.getSeconds();
    if (seconds < 10) {
      seconds = "0" + seconds;
    }
    if (minutes < 10) {
      minutes = "0" + minutes;
    }
    if (hours < 10) {
      hours = "0" + hours;
    }
    return hours + ":" + minutes + ":" + seconds;
  }
  let timestamp = currentDate.getTime() / 1000;
  let difference = timestamp - startTime;
  let minutes = difference / 60;
  minutes = Math.floor(minutes);
  let seconds = difference - minutes * 60;
  seconds = Math.round(seconds);
  if (seconds < 10) {
    seconds = "0" + seconds;
  }
  let string = minutes.toString() + ":" + seconds.toString();

  return string;
}

//open a new window with specified url
function newWindow(url) {
  window.open(url, "", "width=600,height=400");
}

//function to add floating window to the screen
function initializeFloatingWindow() {

  //get the time tracking window
  trackerWindow();

  //add the timer to the page
  setInterval(function() {
      document.querySelector('#timer').innerHTML = minutesSince(document.getElementById('timerValue').value)
    }, 1000);

  setInterval(function() {
      document.querySelector('#timer2').innerHTML = minutesSince(document.getElementById('timerValue').value)
    }, 1000);

//todo work out the style sheets

    getPossibleTags();
}

//add the ongoing time to each entry that is open.
function setOngoingEntries(nodes, time) {
  for (let i = 0; i < nodes.length; i++) {
    let nextCell = nodes[i].parentNode.nextElementSibling;
    nextCell.innerHTML = minutesSince(time);
  }
}

//function to export a table when you click on the heading row.
function tableToCSV(tableTr) {
  //create a variable for the CSV
  let csv = "";

  //get the Parent table for the TR that we have selected.
  let table = tableTr.parentNode;
  let rows = table.querySelectorAll("tr");

  //loop through each row
  rows.forEach((row) => {
    let cells = row.querySelectorAll("td, th");

    //loop through each cell
    cells.forEach((cell) => {
      csv += cell.innerHTML + "|";
    });

    //get rid of the trailing separator
    csv = csv.slice(0, -1);
    //add new lines
    csv += "\n";
  });

  //create the file
  csvFile = new Blob([csv], { type: "text/csv" });

  //create a button and click it
  let downloadLink = document.createElement("a");

  //add a url to the CSV
  downloadLink.download = "time_tracking_export.csv";
  let url = window.URL.createObjectURL(csvFile);
  downloadLink.href = url;
  //hide it
  downloadLink.style.display = "none";
  //add it to the body so we can click it
  document.body.appendChild(downloadLink);

  //click it
  downloadLink.click();

  //cleanup
  document.body.removeChild(downloadLink);
}

//functions to get possible tags from the DB
function getPossibleTags() {


  let xhr = new XMLHttpRequest();
  xhr.open("GET", "../ajax/get_tags.php");

  xhr.onload = function() {
    if (xhr.status === 200) {
      possibleTags = JSON.parse(xhr.responseText);
      console.log(possibleTags);
    }
  }

  xhr.send();
}



//match the input against a array and return the possible matches
function matchATag(input) {
  //error catch
  if (input == "") {
    return [];
  }

  //filter against the tags array
  let needle = new RegExp(input.toLowerCase());
  return possibleTags.filter((tag) => {
    tag = tag.toLowerCase();
    if (tag.match(needle)) {
      return tag;
    }
  });
}
//search tags and then display them to the screen
function showTags(input) {
  //get the div
  div = document.getElementById("divTags");
  div.innerHTML = "";

  let list = "<ul class=tags>";

  // get the matched tags
  let tags = matchATag(input);

  tags.forEach((tag) => {
    list += "<li onclick='addTag(this.innerHTML)'>" + tag + "</li>";
  });

  list += "</ul>";
  //display the results
  console.log("ShowTags function ran list value is" + list + " the input was " + input);
  div.innerHTML = list;
}

//function to add a tag to the hidden input for entry to the db
function addTag(tag) {
  hiddenInput = document.getElementById("tags");
  currentTags = hiddenInput.value;
  //add | to the tag so that each tag is contained
  tag = "|" + tag + "|,";

  if (currentTags.includes(tag)) {
    console.log(tag + " is already on this entry");
    alert(tag + " is already on this entry");
  } else {
    hiddenInput.value += tag;
  }
  displayTags();
}

//function to display buttons for the tags on an entry
function displayTags() {
  let tagsArray = getCurrentTags();

  let displayTagsDiv = document.getElementById("displayTags") ? document.getElementById("displayTags") : null;
  if (!displayTagsDiv) {
    return;
  }
  displayTagsDiv.innerHTML = "";

  tagsArray.forEach((tag) => {
    let div = document.createElement("div");
    div.className = "tag";
    div.setAttribute("onclick", "removeTag('" + tag + "')")
    div.textContent = tag.replaceAll("|", "");
    displayTagsDiv.appendChild(div);
  });
}

//remove a tag from the list of current tags
function removeTag(tag) {
  //get array of current tags
  let tagsArray = getCurrentTags();

  //find the tag in the array
  let index = tagsArray.indexOf(tag);

  //remove the tag from the array
  if (index != -1) {
  tagsArray.splice(index, 1);
  }
  //clear the Value of hidden input
  let hiddenInput = document.getElementById("tags");
  hiddenInput.value = "";

  //write the array back to hidden input
  hiddenInput.value = arrayToCSV(tagsArray);
  displayTags();
}

//function get array of current tags
function getCurrentTags() {
  //get the hidden input with the current tags
  let hiddenInput = document.getElementById ? document.getElementById("tags") : null;
  //if the hidden input does not exist return an empty array
  if (!hiddenInput) {
    return [];
  }
  //get the value of the hidden input
  let currentTags = hiddenInput.value;
  //add a function to loop through and get each tag from the value
  let tagsArray = currentTags.split(",");
  tagsArray.pop();

  return tagsArray;
}


//function to convert an array to a CSV
function arrayToCSV(array) {
  let string = "";
  array.forEach(item => {
    string += item + ",";
  })

  return string;
}



//generic function to display a message for 1-2 seconds on the screen so I do not use alert.
function displayMessage(message) {
  let messageDiv = document.getElementById("message");
  messageDiv.style.display = "flex";
  let messageDOM = document.createElement("span");
  messageDOM.innerHTML = message;
  messageDiv.appendChild(messageDOM);
  setTimeout(() => {
    messageDOM.remove();
    messageDiv.style.display = "none";
  }, 5000);
}


//add a function to display a floating start stop window or refresh it
function trackerWindow() {
  let trackerWindow = document.getElementById("trackerWindow");
  
  //set the tracker window to visible
  trackerWindow.style.display = "flex";



  let xhr = new XMLHttpRequest();
  xhr.open("POST", "../ajax/tracker.php");
  xhr.setRequestHeader("Content-Type", "application/json");
  
  xhr.onload = function() {
    if (xhr.status === 200) {
      let result = xhr.responseText;
      trackerWindow.innerHTML = result;
      initQuillEditors(trackerWindow);
      //wait a bit for the DOM to be ready and then add the tags
      setTimeout(displayTags(), 500);
      //check if I am looking at a project and add the option to link to it
      setTimeout(displayLinkToProject(), 500);
      //change the heading color
      checkForOpenJob();
    } else {
      console.log("Search failed with status: " + xhr.status);
    }

  }

  xhr.send();
}


//get entries data and return JSON for updating the table
function getEntriesForm() {
  syncQuillInputs();

  let id = document.getElementById("entryId") ? document.getElementById("entryId").value : null;
  //this is the current category if it is a open job
  let currentCat = document.getElementById("curEntryCat") ? document.getElementById("curEntryCat").value : null;
  //this is the new category if it is a open job that has been interrupted
  let category = document.getElementById("categories").value;
  let startTime = document.getElementById("timerValue").value;
  let comment = document.getElementById("comment") ? document.getElementById("comment").value : "";
  let interrupted = "";
  let interruptedDOM = document.getElementById("interrupted") ? document.getElementById("interrupted").checked : false;
  if (interruptedDOM) {
    interrupted = "Y";
  } else { 
    interrupted = "N";
  }
  let project = document.getElementById("project_id") ? document.getElementById("project_id").value : null;
  //if there is no project_id then it may be a new entry
  if (!project) {
    project = document.getElementById("linkToProjectCheckbox") ? document.getElementById("linkToProjectCheckbox").checked : false;
    if (project) {
      project = getCurrentProjectID();
    } else {
      project = null;
    }
  }
  let tags = document.getElementById("tags") ? document.getElementById("tags").value : null;


  data = {
    "id": id,
    "category": category,
    "category_display": currentCat,
    "start_time": startTime,
    "comment": comment,
    "interrupted": interrupted,
    "project_id": project,
    "tags": tags
  };

  return data;

}

// add a function to start the timer
function startTimer() {
  //get all the data from the form and send it to the server
  let data = getEntriesForm();
  console.log(data);

  let xhr = new XMLHttpRequest();
  xhr.open("POST", "../ajax/start_work.php");
  xhr.setRequestHeader("Content-Type", "application/json");
  
  xhr.onload = function() {
    if (xhr.status === 200) {
      let result = xhr.responseText;
      displayMessage("Time Tracker started");
      console.log(result);
      trackerWindow();
      toggleNavColor();
    } else {
      displayMessage("Failed to start timer with status: " + xhr.status);
      console.log("start work failed with status: " + xhr.status);
    }

  }

  xhr.send(JSON.stringify(data));
  displayMessage("Timer Starting");
}

//function to stop the timer
function stopTimer() {
  //get all the data from the form and send it to the server
  let data = getEntriesForm();
  
  //if the project_id is new then create a new project
  if(data.project_id == "new") {
    let projectName = "TODO - " + data.category_display;
    let projectDesc = "project created from an entry, details are: " + data.comment;
    
    createNewProject(projectName, projectDesc)
      .then((newProjectId) => {
        data.project_id = newProjectId;

        let xhr = new XMLHttpRequest();
        xhr.open("POST", "../ajax/stop_work.php");
        xhr.setRequestHeader("Content-Type", "application/json");
        
        xhr.onload = function() {
          if (xhr.status === 200) {
            let result = xhr.responseText;
            displayMessage("Time Tracker stopped");
            console.log(result);
            trackerWindow();
            toggleNavColor();
          } else {
            displayMessage("Failed to stop timer with status: " + xhr.status);
            console.log("stop work failed with status: " + xhr.status);
          }
        }

        xhr.send(JSON.stringify(data));
        displayMessage("Timer Stopping");
      })
      .catch((error) => {
        displayMessage("Failed to create new project: " + error.message);
        console.log("Failed to create new project: " + error.message);
      });
  } else {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "../ajax/stop_work.php");
    xhr.setRequestHeader("Content-Type", "application/json");
    
    xhr.onload = function() {
      if (xhr.status === 200) {
        let result = xhr.responseText;
        displayMessage("Time Tracker stopped");
        console.log(result);
        trackerWindow();
        toggleNavColor();
      } else {
        displayMessage("Failed to stop timer with status: " + xhr.status);
        console.log("stop work failed with status: " + xhr.status);
      }
    }

    xhr.send(JSON.stringify(data));
    displayMessage("Request Timer Stopped");
  }
}

//save a entry without stopping it
function saveEntry() {
  //get all the data from the form and send it to the server
  let data = getEntriesForm();
  console.log(data);

  //if the project_id is new then create a new project
  if(data.project_id == "new") {
    let projectName = "TODO - " + data.category_display;
    let projectDesc = "project created from an entry, details are: " + data.comment;
    
    createNewProject(projectName, projectDesc)
      .then((newProjectId) => {
        data.project_id = newProjectId;

        let xhr = new XMLHttpRequest();
        xhr.open("POST", "../ajax/save_entry.php");
        xhr.setRequestHeader("Content-Type", "application/json");
        
        xhr.onload = function() {
          if (xhr.status === 200) {
            let result = xhr.responseText;
            displayMessage("Entry saved");
            //refresh the tracker window with the new project
            trackerWindow();
            console.log(result);
          } else {
            console.log("save entry failed with status: " + xhr.status);
          }

        }

        xhr.send(JSON.stringify(data));
        displayMessage("Entry Saving");
      })
      .catch((error) => {
        displayMessage("Failed to create new project: " + error.message);
        console.log("Failed to create new project: " + error.message);
      });
  } else {
    let xhr = new XMLHttpRequest();
        xhr.open("POST", "../ajax/save_entry.php");
        xhr.setRequestHeader("Content-Type", "application/json");
        
        xhr.onload = function() {
          if (xhr.status === 200) {
            let result = xhr.responseText;
            displayMessage("Entry Saved");
            console.log(result);
          } else {
            console.log("save entry failed with status: " + xhr.status);
          }

        }

        xhr.send(JSON.stringify(data));
        displayMessage("Entry Saving");
  }
}








function toggleCurrentJobView() {
  let currentJob = document.querySelector('.current-job');
  let currentJobMini = document.querySelector('.current-job-mini');

  //todo change this to flex
  if (currentJob.style.display === 'none') {
    currentJob.style.display = 'block';
    currentJobMini.style.display = 'none';
  } else {
    currentJob.style.display = 'none';
    currentJobMini.style.display = 'block';
  }
}



//function to create a new project from a entry if the project drop list is new
function createNewProject(projectName, ProjectDesc) {
  return new Promise((resolve, reject) => {
    console.log("Creating a new project");
    //send more data
    let data = {
      id: "",
      title: projectName,
      dateCreated: "",
      editorContent: ProjectDesc,
      project_cat: 0,
      dateClosed: ""
    };
    console.log(data);

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "../ajax/project_save.php");
    xhr.setRequestHeader("Content-Type", "application/json");

    xhr.onload = function() {
      if (xhr.status === 200) {
        let result = xhr.responseText;
        displayMessage("Project created");
        console.log(result);
        let resultParts = result.split('=');
        let id = resultParts.pop();
        resolve(id);
      } else {
        console.log("create project failed with status: " + xhr.status);
        reject("create project failed with status: " + xhr.status);
      }
    };

    xhr.onerror = function() {
      reject("Network error");
    };

    xhr.send(JSON.stringify(data));
    displayMessage("Project Creating");
  });
}




function checkForOpenJob() {
  //get the header
  let navBar = document.querySelector('nav');

  let xhr = new XMLHttpRequest();
  xhr.open("GET", "../ajax/check_open_job.php");

  xhr.onload = function() {
    if (xhr.status === 200) {
      let result = xhr.responseText;
      console.log(result);
      if (result == "TRUE") {
        displayMessage("You have an open job");
        //change the nav class to active
        navBar.classList.add('active');
        
      } else {
        displayMessage("No Open Job");
        //remove the nav class active
        navBar.classList.remove('active');
      }
    } else {
      console.log("check open job failed with status: " + xhr.status);
    }
  }

  xhr.send();
}

//add a function to toggle the nav color
function toggleNavColor() {
  let navBar = document.querySelector('nav');
  navBar.classList.toggle('active');
}

// Cycle light → dark → system and persist via settings.
function cycleThemeMode() {
  let xhr = new XMLHttpRequest();
  xhr.open("POST", "../ajax/theme_mode.php", true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState !== 4) {
      return;
    }
    if (xhr.status !== 200) {
      console.log("theme mode update failed with status: " + xhr.status);
      return;
    }
    let data;
    try {
      data = JSON.parse(xhr.responseText);
    } catch (e) {
      console.log("theme mode update returned invalid JSON");
      return;
    }
    if (data.theme_mode === "light" || data.theme_mode === "dark") {
      document.documentElement.setAttribute("data-theme", data.theme_mode);
    } else {
      document.documentElement.removeAttribute("data-theme");
    }
    displayMessage("Theme: " + data.theme_mode);
  };
  xhr.send();
}


//get the current project ID so that you can start a entry straight to the project
function getCurrentProjectID() {
  let id = document.getElementById('id') ? document.getElementById('id').value : null;
  return id;
}

//display a button to add a new entry to the project
function displayLinkToProject() {
  //todo check if dom exists
  let linkDiv = document.getElementById('linkToProject');
  let id = getCurrentProjectID();
  if (id) {
    linkDiv.innerHTML = `<label for="linkToProject">Add Entry to Project</label><input type="checkbox" id="linkToProjectCheckbox" checked>`;
  }
}

// Persist home layout preference and reload.
function switchHomeView(view) {
  let xhr = new XMLHttpRequest();
  xhr.open("POST", "../ajax/home_view.php");
  xhr.setRequestHeader("Content-Type", "application/json");
  xhr.onload = function () {
    if (xhr.status === 200) {
      window.location.href = "index.php";
    } else {
      displayMessage("Failed to switch home view");
    }
  };
  xhr.send(JSON.stringify({ home_view: view }));
}

// Compact home: show/hide the main nav behind the hamburger.
function toggleNavMenu(forceOpen) {
  let nav = document.getElementById("mainNav");
  let btn = document.getElementById("navHamburger");
  if (!nav || !btn) {
    return;
  }
  let open = typeof forceOpen === "boolean" ? forceOpen : !nav.classList.contains("is-open");
  nav.classList.toggle("is-open", open);
  btn.setAttribute("aria-expanded", open ? "true" : "false");
}

document.addEventListener("click", function (event) {
  let nav = document.getElementById("mainNav");
  if (!nav || !nav.classList.contains("is-open")) {
    return;
  }
  if (!nav.contains(event.target)) {
    toggleNavMenu(false);
  }
});

// Persist calendar day/week span and reload.
function switchCalendarSpan(span) {
  let xhr = new XMLHttpRequest();
  xhr.open("POST", "../ajax/home_view.php");
  xhr.setRequestHeader("Content-Type", "application/json");
  xhr.onload = function () {
    if (xhr.status === 200) {
      let params = new URLSearchParams(window.location.search);
      let day = params.get("day");
      window.location.href = day ? "index.php?day=" + encodeURIComponent(day) : "index.php";
    } else {
      displayMessage("Failed to switch calendar span");
    }
  };
  xhr.send(JSON.stringify({ calendar_span: span }));
}

function compactSelectedCategory() {
  let select = document.querySelector("#compactTracker #categories, .cal-mini-tracker #categories, #homeCompact #categories");
  return select ? select.value : null;
}

function initCompactTrackerUi(root) {
  root = root || document.getElementById("compactTracker") || document;
  initQuillEditors(root);
  displayTags();
  if (typeof possibleTags === "undefined" || !possibleTags || !possibleTags.length) {
    getPossibleTags();
  }
}

function compactReadFields() {
  let root = document.getElementById("compactTracker") || document;
  syncQuillInputs(root);
  let commentEl = document.getElementById("comment");
  let tagsEl = document.getElementById("tags");
  let interruptedEl = document.getElementById("interrupted");
  return {
    comment: commentEl ? commentEl.value : "",
    tags: tagsEl ? tagsEl.value : "",
    interrupted: interruptedEl && interruptedEl.checked ? "Y" : "N",
  };
}

function compactPayload(forceInterrupted) {
  let curCat = document.getElementById("compactCurCat");
  let currentCat = curCat ? curCat.value : null;
  let nextCat = compactSelectedCategory();
  let fields = compactReadFields();
  let interrupted = forceInterrupted || fields.interrupted;
  return {
    category: nextCat,
    category_display: currentCat || nextCat,
    comment: fields.comment,
    interrupted: interrupted,
    project_id: "",
    tags: fields.tags,
  };
}

function refreshCompactPanel() {
  let homeCompact = document.getElementById("homeCompact");
  // Calendar grid must stay in sync with open/closed jobs.
  if (!homeCompact && document.getElementById("homeCalendar")) {
    window.location.reload();
    return;
  }
  if (!homeCompact) {
    window.location.reload();
    return;
  }

  let xhr = new XMLHttpRequest();
  xhr.open("GET", "../ajax/compact_panel.php?mode=full");
  xhr.onload = function () {
    if (xhr.status !== 200) {
      displayMessage("Failed to refresh tracker");
      return;
    }
    homeCompact.innerHTML = xhr.responseText;
    let timerValue = document.getElementById("timerValue");
    if (timerValue) {
      window.__homeOpenJobStart = timerValue.value;
    }
    initCompactTrackerUi(homeCompact);
    checkForOpenJob();
  };
  xhr.send();
}

function compactStartJob() {
  let category = compactSelectedCategory();
  if (!category) {
    displayMessage("Select a category first");
    return;
  }
  let data = {
    category: category,
    comment: "",
    tags: "",
    interrupted: "N",
    project_id: "",
  };
  let xhr = new XMLHttpRequest();
  xhr.open("POST", "../ajax/start_work.php");
  xhr.setRequestHeader("Content-Type", "application/json");
  xhr.onload = function () {
    if (xhr.status === 200) {
      displayMessage("Time Tracker started");
      refreshCompactPanel();
      toggleNavColor();
    } else {
      displayMessage("Failed to start timer");
    }
  };
  xhr.send(JSON.stringify(data));
}

function compactStopJob() {
  let data = compactPayload(null);
  if (!data.category_display) {
    displayMessage("No open job to stop");
    return;
  }
  // Plain stop: keep interrupted from checkbox; do not open a new job unless checked.
  let xhr = new XMLHttpRequest();
  xhr.open("POST", "../ajax/stop_work.php");
  xhr.setRequestHeader("Content-Type", "application/json");
  xhr.onload = function () {
    if (xhr.status === 200) {
      displayMessage("Time Tracker stopped");
      refreshCompactPanel();
      toggleNavColor();
    } else {
      displayMessage("Failed to stop timer");
    }
  };
  xhr.send(JSON.stringify(data));
}

function compactSwitchJob() {
  let data = compactPayload("Y");
  if (!data.category) {
    displayMessage("Select a category to switch to");
    return;
  }
  if (String(data.category) === String(data.category_display)) {
    displayMessage("Pick a different category to switch");
    return;
  }
  let xhr = new XMLHttpRequest();
  xhr.open("POST", "../ajax/stop_work.php");
  xhr.setRequestHeader("Content-Type", "application/json");
  xhr.onload = function () {
    if (xhr.status === 200) {
      displayMessage("Switched job");
      refreshCompactPanel();
      toggleNavColor();
    } else {
      displayMessage("Failed to switch job");
    }
  };
  xhr.send(JSON.stringify(data));
}

function focusCalendarEntry(entryId) {
  let block = document.querySelector('.cal-block[data-entry-id="' + entryId + '"]');
  if (!block) {
    newWindow("entries.php?id=" + entryId);
    return;
  }
  document.querySelectorAll(".cal-block.is-focused").forEach(function (el) {
    el.classList.remove("is-focused");
  });
  block.classList.add("is-focused");
  block.scrollIntoView({ behavior: "smooth", block: "center" });
}