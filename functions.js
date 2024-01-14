// functions in js
// V1.1 Created 2024-01-06 By MM - First version

function minutesSince(startTime) {
  let currentDate = new Date();
  if(startTime === "No Open Job") {
    let hours = currentDate.getHours();
    let minutes = currentDate.getMinutes();
    let seconds = currentDate.getSeconds();
    if(seconds < 10) {
      seconds = "0" + seconds;
    }
    if(minutes < 10) {
      minutes = "0" + minutes;
    }
    if(hours < 10) {
      hours = "0" + hours;
    }
    return hours + ":" + minutes + ":" + seconds;
    console.log(hours + ":" + minutes + ":" + seconds);
  }
  let timestamp = currentDate.getTime() / 1000;
  let difference = timestamp - startTime;
  let minutes = difference / 60;
  minutes = Math.floor(minutes);
  let seconds = difference - (minutes * 60);
  seconds = Math.round(seconds);
  if(seconds < 10) {
    seconds = "0" + seconds;
  }
  let string = minutes.toString() + ":" + seconds.toString();

  return string;
}

//open a new window with specified url
function newWindow(url){
  window.open(url, "", "width=600,height=400");
}