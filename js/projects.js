// functions for the projects side of the project
// Created 2024-11-13 By MM - First version

function saveProject() {
    // Get form elements
    let id = document.getElementById('id').value;
    let title = document.getElementById('title').value;
    let project_category = document.getElementById('project_cat').value;
    let dateCreated = document.getElementById('date_created').value;
    let dateClosed = document.getElementById('date_closed').value;

    //todo get the category and save it
  
    // Get editor content
    let editorContent = quill.getSemanticHTML();


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


