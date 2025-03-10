<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
    <style>
        #calendar {
            max-width: 500px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="text-center my-4">Calendar</h2>
        <div id='calendar'></div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Add Event</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="eventForm">
                        <div class="form-group">
                            <label for="eventDate">Date</label>
                            <input type="text" class="form-control" id="eventDate" readonly>
                        </div>
                        <div class="form-group">
                            <label for="eventDescription">Event Description</label>
                            <textarea class="form-control" id="eventDescription" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 350,
                dateClick: function(info) {
                    $('#eventDate').val(info.dateStr);
                    $('#eventModal').modal('show');
                }
            });
            calendar.render();
        });

        $('#eventForm').on('submit', function(event) {
            event.preventDefault();
            var date = $('#eventDate').val();
            var description = $('#eventDescription').val();
            alert('Event on ' + date + ': ' + description);
            $('#eventModal').modal('hide');
        });
    </script>
</body>

</html>