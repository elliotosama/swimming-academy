function calculateWorkingDays(firstSession, workingDays, planDays, numberOfSessions = 2) {
  let sessionCount = 2;
  let sessionsDate = [firstSession];
  let date = new Date(firstSession);
  let dayOneName = date.toLocaleDateString('en-US', { weekday: 'long' }); // will need it later

  // handle 2 per week scenario
  if(numberOfSessions === 2) {
    let dayTwo = workingDays.daysArray[0]
    if(dayTwo === dayOneName) {
      dayTwo = workingDays.daysArray[1];
    }
    while(sessionCount <= planDays) {
      date.setDate(date.getDate() + 1)
      let currentDayName = date.toLocaleDateString('en-US', { weekday: "long"})
      const formatted = date.toISOString().split('T')[0];
      if(currentDayName === dayTwo || currentDayName == dayOneName) {
        sessionsDate.push(formatted);
        sessionCount++;
      }
    }
    return {
      renewal: sessionsDate[sessionsDate.length - 2],
      last: sessionsDate[sessionsDate.length - 1]
    }
  } else if(numberOfSessions === 1) {
    // handle 1 session a week scenario
    while (sessionCount <= planDays) {
      date.setDate(date.getDate() + 1)
      let currentDayName = date.toLocaleDateString('en-US', { weekday: "long"})
      const formatted = date.toISOString().split('T')[0];
      if(currentDayName == dayOneName) {
        sessionsDate.push(formatted);
        sessionCount++;
      }
    }
    return {
      renewal: sessionsDate[sessionsDate.length - 2],
      last: sessionsDate[sessionsDate.length - 1]
    }
  }
} 


function capitalize(str) {
    if (!str || typeof str !== "string") return "";
    return str.charAt(0).toUpperCase() + str.slice(1);
}
