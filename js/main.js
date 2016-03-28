/**
 * Created by Doublefinger on 3/27/16.
 */

// Get hidden token
$(document).ready(function () {
    "use strict";
    $.get("php/token.php", function (data) {
        $('#token').attr("value", data.token);
    }, "json");
});
var currentMonth = getTodayDate();
updateCalendar();
var dates;
var events;
var eventStartDates = [];
var currentEventId = -1;
var clickFlag = false;

$("#log_out").click(function () {
    "use strict";
    $.post("php/logout.php");
});

$("#today_btn").click(function () {
    "use strict";
    currentMonth = getTodayDate();
    updateCalendar();
});

$("#prev_month_btn").click(function () {
    "use strict";
    currentMonth = currentMonth.prevMonth();
    updateCalendar();
});

$("#next_month_btn").click(function () {
    "use strict";
    currentMonth = currentMonth.nextMonth();
    updateCalendar();
});

$('#myModal').on('shown.bs.modal', function (event) {
    "use strict";
    if (clickFlag) {
        clickFlag = false;
        return;
    }
    currentEventId = -1;
    var td = $(event.relatedTarget);
    // Extract info from data-date attributes
    var date = td.context.dataset.date;
    $('#myModalLabel').text("Event");
    $('#event-description').val("");
    $('#deleteEvent').attr("disabled", "disabled");
    $('#editEvent').text("Create event");
    $('#event-date').val(date);
});

// Switch between am-pm
$(".dropdown-menu li a").click(function () {
    "use strict";
    $(this).parents(".input-group-btn").find('.btn').html($(this).text() + ' <span class="caret"></span>');
    $(this).parents(".input-group-btn").find('.btn').attr("value", $(this).text());
});

$('#deleteEvent').click(function () {
    "use strict";
    var token = $('#token').val();
    $.post("php/manageEvent.php", {
        func: "delete",
        id: currentEventId,
        token: token
    }, function () {
        updateCalendar();
        $('#myModal').modal('hide');
    });
});

$('#editEvent').click(function () {
    "use strict";
    var date = $('#event-date').val();
    var start = $('#event-start').val();
    var end = $('#event-end').val();
    var start_am_pm = $('#date-start').val();
    var end_am_pm = $('#date-end').val();
    var description = $('#event-description').val();
    var token = $('#token').val();

    if (description == "") {
        showAlert("Event description cannot be empty.");
        return;
    }
    var pattern = new RegExp("^([0-9]|0[0-9]|1[0-1]):[0-5][0-9]$");

    if (!pattern.test(start) || (!pattern.test(end) && end != "")) {
        showAlert("Invalid time.");
        return;
    }

    var date_time;
    date_time = date + " " + start + ":00";
    start = new Date(date_time);

    if (end != "") {
        date_time = date + " " + end + ":00";
        end = new Date(date_time);
    }
    if (start_am_pm == "pm") {
        start.setHours(start.getHours() + 12);
    }
    if (end_am_pm == "pm" && end != "") {
        end.setHours(end.getHours() + 12);
    }
    if (end != "" && start.getTime() > end.getTime()) {
        showAlert("Invalid time.");
        return;
    }

    var tzoffset = ((new Date()).getTimezoneOffset() + 60) * 60000;
    start = new Date(start - tzoffset).toISOString();
    if (end != "") {
        end = new Date(end - tzoffset).toISOString();
    }
    if (currentEventId >= 0) {
        $.post("php/manageEvent.php", {
            func: "edit",
            id: currentEventId,
            start: start,
            end: end,
            description: description,
            token: token
        }, function () {
            updateCalendar();
            $('#myModal').modal('hide');
        });
    } else {
        $.post("php/manageEvent.php", {
            func: "create",
            start: start,
            end: end,
            description: description,
            token: token
        }, function () {
            updateCalendar();
            $('#myModal').modal('hide');
        });
    }
});

function getTodayDate() {
    "use strict";
    var today = new Date();
    return new Month(today.getFullYear(), today.getMonth());
}

function updateEvents() {
    "use strict";
    $('#date').text(convertMonthToFullName(currentMonth.month) + " " + currentMonth.year);
    $('#calendarBody').find('td').each(function (i) {
        //remove previous color first
        $(this).innerHTML = "";
        $(this).css("color", "");
        var week_index = i / 7;
        if (i % 7 == 0) {
            dates = currentMonth.getWeeks()[week_index].getDates();
        }
        var currentDate = dates[i % 7];
        var content = "";
        if (currentDate.getDate() == 1) {
            content += convertMonthToAbbr(currentDate.getMonth()) + " ";
        }

        if (currentDate.getMonth() != currentMonth.month) {
            $(this).css("color", "grey");
        }
        $(this).text(content + currentDate.getDate());
        $(this).attr("data-date", convertMonthToFullName(currentDate.getMonth()) + " " + currentDate.getDate() + ", " + currentMonth.year);
        var eventDate;
        for (eventDate in eventStartDates) {
            if (eventStartDates.hasOwnProperty(eventDate)) {
                if (eventDate.getDate() == currentDate.getDate() && eventDate.getMonth() == currentDate.getMonth()) {
                    var button = document.createElement('button');
                    button.setAttribute("class", "btn btn-info btn-xs event-button");
                    button.setAttribute("id", events[k].id);
                    button.onclick = function () {
                        clickFlag = true;
                        currentEventId = $(this).attr("id");
                        var i;
                        for (i = 0; i < events.length; i++) {
                            if (events[i].id == currentEventId) {
                                break;
                            }
                        }
                        $('#myModalLabel').text(events[i].description.substring(0, 50));
                        $('#event-description').val(events[i].description);
                        $('#deleteEvent').removeAttr("disabled");
                        $('#editEvent').text("Save changes");
                        $('#event-date').val($(this).parent()[0].dataset.date);
                        $('#myModal').show();
                    };
                    var minutes = "" + eventDate.getMinutes();
                    if (eventDate.getMinutes() < 10) {
                        minutes = "0" + minutes;
                    }
                    button.appendChild(document.createTextNode(eventDate.getHours() + ":" + minutes + " " + events[k].description));
                    $(this).append(button);
                }
            }

        }
    });
}

function updateCalendar() {
    var firstDate = currentMonth.getWeeks()[0].getDates()[0];
    $.post("php/manageEvent.php", {
        contentType: "application/json",
        func: "display",
        month: firstDate.getMonth(),
        date: firstDate.getDate(),
        year: firstDate.getFullYear(),
        dataType: "json"
    }, function (data) {
        events = data;
        eventStartDates = [];
        var e;
        for (e in events) {
            if (events.hasOwnProperty(e)) {
                eventStartDates.push(new Date(e.start));
            }
        }

        updateEvents();
    });
}

function showAlert(message) {
    $('.alert').remove();
    $('.modal-body').prepend('<div class="alert alert-danger" id="warning">' +
        '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>' +
        '<strong>Error!</strong> ' + message + '</div>');
}

function convertMonthToAbbr(month) {
    var name;
    switch (month) {
        case 0:
            name = "Jan";
            break;
        case 1:
            name = "Feb";
            break;
        case 2:
            name = "Mar";
            break;
        case 3:
            name = "Apr";
            break;
        case 4:
            name = "May";
            break;
        case 5:
            name = "Jun";
            break;
        case 6:
            name = "Jul";
            break;
        case 7:
            name = "Aug";
            break;
        case 8:
            name = "Sep";
            break;
        case 9:
            name = "Oct";
            break;
        case 10:
            name = "Nov";
            break;
        case 11:
            name = "Dec";
            break;
    }
    return name;
}

function convertMonthToFullName(month) {
    var name;
    switch (month) {
        case 0:
            name = "January";
            break;
        case 1:
            name = "February";
            break;
        case 2:
            name = "March";
            break;
        case 3:
            name = "April";
            break;
        case 4:
            name = "May";
            break;
        case 5:
            name = "June";
            break;
        case 6:
            name = "July";
            break;
        case 7:
            name = "August";
            break;
        case 8:
            name = "September";
            break;
        case 9:
            name = "October";
            break;
        case 10:
            name = "November";
            break;
        case 11:
            name = "December";
            break;
    }
    return name;
}