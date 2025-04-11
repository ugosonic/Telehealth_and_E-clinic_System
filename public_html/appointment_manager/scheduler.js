document.addEventListener("DOMContentLoaded", function() {
    const calendar = document.getElementById("calendar");
    const form = document.querySelector("form");
    const dateInput = document.getElementById("selected-date");
    const timeSlots = document.getElementById("time-slots");
    
    function createCalendar() {
        for (let i = 1; i <= 30; i++) {
            const day = document.createElement("div");
            day.innerText = i;
            day.addEventListener("click", function() {
                document.querySelectorAll("#calendar div").forEach(d => d.classList.remove("selected"));
                day.classList.add("selected");
                dateInput.value = new Date().getFullYear() + "-" + (new Date().getMonth() + 1).toString().padStart(2, '0') + "-" + i.toString().padStart(2, '0');
                form.classList.add("active");
                loadTimeSlots();
            });
            calendar.appendChild(day);
        }
    }

    function loadTimeSlots() {
        timeSlots.innerHTML = "";
        const times = generateTimeSlots();
        times.forEach(time => {
            const option = document.createElement("option");
            option.value = time;
            option.text = time;
            timeSlots.appendChild(option);
        });
    }

    function generateTimeSlots() {
        const slots = [];
        let currentTime = new Date();
        currentTime.setHours(8, 0, 0, 0);
        const endTime = new Date();
        endTime.setHours(17, 0, 0, 0);
        while (currentTime <= endTime) {
            const timeString = currentTime.toTimeString().slice(0, 5);
            slots.push(timeString);
            currentTime.setMinutes(currentTime.getMinutes() + 90);
        }
        return slots;
    }

    createCalendar();
});
