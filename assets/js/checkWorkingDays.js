async function checkWorkginDays(branchId, selectedDate) {
    let response = await fetch(`/ajax/get_branch_days.php?id=${branchId}`);
    let data = await response.json();

    if (!data.days) return false;

    const workingDays = data.days;
  let selectedDay = new Date(selectedDate);
  let dayOneName = selectedDay.toLocaleDateString('en-US', { weekday: 'long' }); // will need it later


    for (let key in workingDays) {
        let value = workingDays[key];

        if (!value) continue; // skip null

        // convert "Sunday,Monday" → ["Sunday", "Monday"]
        let daysArray = value.split(',').map(day => day.trim());

        if (daysArray.includes(dayOneName)) {
            return {
                daysArray: daysArray
            };
        }
    }

    return false;
}