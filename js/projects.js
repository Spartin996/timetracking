// functions for the projects side of the project
// Created 2024-11-13 By MM - First version

const projectQuillToolbar = [
  [{ list: "check" }, { list: "bullet" }, { list: "ordered" }],
  ["bold", "underline", "strike"],
  ["code-block"],
  ["link", "image"],
  [{ header: 2 }],
  [{ indent: "-1" }, { indent: "+1" }],
  [{ color: [] }, { background: [] }],
  ["clean"],
];

function initProjectEditor(root) {
  root = root || document;
  if (typeof Quill === "undefined") {
    return;
  }
  let editor = (root.querySelector && root.querySelector("#editor")) || document.getElementById("editor");
  if (!editor) {
    return;
  }
  window.quill = new Quill(editor, {
    modules: {
      toolbar: projectQuillToolbar,
    },
    theme: "snow",
  });
}

function isSplitPage() {
  return !!document.getElementById("homeSplit");
}

function setSplitReturnProject(id) {
  let el = document.getElementById("return_project");
  if (el) {
    el.value = id || "";
  }
}

function replaceSplitProjectQuery(id) {
  if (!isSplitPage() || !window.history || !window.history.replaceState) {
    return;
  }
  let url = new URL(window.location.href);
  if (id) {
    url.searchParams.set("project", id);
  } else {
    url.searchParams.delete("project");
  }
  window.history.replaceState({}, "", url);
}

function highlightSplitProject(id) {
  document.querySelectorAll(".split-project-item").forEach(function (el) {
    el.classList.toggle("is-active", String(el.getAttribute("data-project-id")) === String(id || ""));
  });
}

function refreshSplitProjectList(selectedId) {
  let list = document.getElementById("splitProjectList");
  if (!list) {
    return;
  }
  let url = "../ajax/project_list.php";
  if (selectedId) {
    url += "?selected=" + encodeURIComponent(selectedId);
  }
  let xhr = new XMLHttpRequest();
  xhr.open("GET", url);
  xhr.onload = function () {
    if (xhr.status === 200) {
      list.innerHTML = xhr.responseText;
      highlightSplitProject(selectedId);
    }
  };
  xhr.send();
}

function loadSplitProject(id) {
  let editorPane = document.getElementById("splitProjectEditor");
  if (!editorPane) {
    return;
  }
  id = id || "";
  let url = "../ajax/project_editor.php";
  if (id !== "") {
    url += "?id=" + encodeURIComponent(id);
  }
  let xhr = new XMLHttpRequest();
  xhr.open("GET", url);
  xhr.onload = function () {
    if (xhr.status === 200) {
      editorPane.innerHTML = xhr.responseText;
      initProjectEditor(editorPane);
      setSplitReturnProject(id);
      highlightSplitProject(id);
      replaceSplitProjectQuery(id);
    } else {
      displayMessage("Failed to load project");
    }
  };
  xhr.send();
}

function onProjectSaved(id) {
  if (!document.getElementById("splitProjectList")) {
    return;
  }
  refreshSplitProjectList(id);
  setSplitReturnProject(id);
  replaceSplitProjectQuery(id);
  highlightSplitProject(id);
}

function saveProject() {
    // Get form elements
    let id = document.getElementById('id').value;
    let title = document.getElementById('title').value;
    let project_category = document.getElementById('project_cat').value;
    let dateCreated = document.getElementById('date_created').value;
    let dateClosed = document.getElementById('date_closed').value;

    //todo get the category and save it
  
    // Get editor content
    let editorContent = (window.quill && window.quill.getSemanticHTML)
      ? window.quill.getSemanticHTML()
      : "";


    let data = {
      id: id,
      title: title,
      project_cat: project_category,
      dateCreated: dateCreated,
      dateClosed: dateClosed,
      editorContent: editorContent
    };
  
    // Do I need to do any sanitation 

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "../ajax/project_save.php");
    xhr.setRequestHeader("Content-Type", "application/json");
  
    xhr.onload = function() {
      if (xhr.status === 200) {
        let result = xhr.responseText;
        displayMessage("Project Saved with Status: " + xhr.status);
        console.log("Search result: ", result);
        if (id === ''){
          //if I do not have a id number get it from the save message
          let resultParts = result.split('=');
          id = resultParts.pop();
          
          //update the hidden input and the display with the id

          let idInput = document.getElementById('id');
          idInput.value = id;

          let idDisp = document.getElementById('idDisp');
          idDisp.innerHTML = "ID: " + id;


        }
        if (typeof onProjectSaved === "function") {
          onProjectSaved(id);
        }
      } else {
        displayMessage("Project Save Failed with Status: " + xhr.status);
        console.log("save failed " + xhr.status);
      }

    }
  
    xhr.send(JSON.stringify(data));
    displayMessage('Request Project Saved');
}



function showAdvancedSearch() {
  let advancedSearchDiv = document.getElementById('advancedSearchOptions');
  let advancedSearchButton = document.getElementById('advancedSearchButton');
  
  if (advancedSearchDiv.style.display === 'flex') {
    advancedSearchDiv.style.display = 'none';
    advancedSearchButton.value = 'Advanced Search';
  } else {
    advancedSearchDiv.style.display = 'flex';
    advancedSearchButton.value = 'Hide Advanced Search';
  }
}


function searchProjects(type) {
  let data = {}

  let projectList = document.getElementById('projectList');


  // If type is standard, just get all open projects.
  if (type === 'standard') {
    data = {
      type: type
    };
  } else {
  // If the type is advanced, get the advanced search options.
  let searchKey = document.getElementById('searchKey').value;
  
  let searchTitle = document.getElementById('searchTitle').checked;
  
  let searchContent = document.getElementById('searchContent').checked;
  
  let includeClosed = document.getElementById('includeClosed').checked;

  let searchRange = getSelectedRadioValue('searchRange');

  let startDate = document.getElementById('startDate').value;

  let endDate = document.getElementById('endDate').value;

  data = {
    type: type,
    searchKey: searchKey,
    searchTitle: searchTitle,
    searchContent: searchContent,
    includeClosed: includeClosed,
    searchRange: searchRange,
    startDate: startDate,
    endDate: endDate
  };

    // Get form elements
    // generate the data
  }

  // do the Ajax call
  let xhr = new XMLHttpRequest();
  xhr.open("POST", "../ajax/project_display.php");
  xhr.setRequestHeader("Content-Type", "application/json");
  
  xhr.onload = function() {
    if (xhr.status === 200) {
      let result = xhr.responseText;
      displayMessage("Project Search Result: " + xhr.status);
      projectList.innerHTML = result;
    } else {
      console.log("Search failed with status: " + xhr.status);
    }

  }

  xhr.send(JSON.stringify(data));
  displayMessage('Request Project Search');
}





function getSelectedRadioValue(name) {
  let radios = document.getElementsByName(name);
  for (let i = 0; i < radios.length; i++) {
    if (radios[i].checked) {
      return radios[i].value;
    }
  }
  return null;
}


function setInputToCurrentDate(inputId) {
  let input = document.getElementById(inputId);
  let date = new Date();
  
  // Format date components
  let year = date.getFullYear();
  let month = String(date.getMonth() + 1).padStart(2, '0');  
  let day = String(date.getDate()).padStart(2, '0');
  let hours = String(date.getHours()).padStart(2, '0');
  let minutes = String(date.getMinutes()).padStart(2, '0');

  // Combine into YYYY-MM-DDTHH:MM format
  let dateString = `${year}-${month}-${day}T${hours}:${minutes}`;
  input.value = dateString;
}


function countCheckboxes(divID) {
  let checkedCount = 0;
  let uncheckedCount = 0;

  // Get all li elements within editor div
  let editorContent = document.getElementById(divID);
  let items = editorContent.querySelectorAll('li');

  // Loop through each li element and count based on data-list attribute
  items.forEach(item => {
    if (item.getAttribute('data-list') === 'checked') {
      checkedCount++;
    } else if (item.getAttribute('data-list') === 'unchecked') {
      uncheckedCount++;
    }
  });

  console.log(`Checked: ${checkedCount}, Unchecked: ${uncheckedCount}`);

  return true
}


