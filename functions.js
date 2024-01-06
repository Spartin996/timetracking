// functions in js
// V1.1 Created 2024-01-06 By MM - First version

function minutesSince(startTime) {
  if(startTime === "No Open Job") {
    return "no difference";
  }
  let currentDate = new Date();
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
