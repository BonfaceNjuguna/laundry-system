<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Reminder</title>
</head>
<body>
    <h2>Booking Reminder</h2>
    <p>Dear Admin,</p>

    <p>This is a reminder about a booking:</p>

    <ul>
        <li><strong>Customer Name:</strong> {{ $booking->customer->name }}</li>
        <li><strong>Service:</strong> {{ $booking->service->name }}</li>
        <li><strong>Location:</strong> {{ $booking->location }}</li>
        <li><strong>Start Date:</strong> {{ $booking->start_date }}</li>
        <li><strong>End Date:</strong> {{ $booking->end_date }}</li>
        <li><strong>Status:</strong> {{ $booking->status }}</li>
        <li><strong>Paid:</strong> {{ $booking->is_paid ? 'Yes' : 'No' }}</li>
    </ul>

    <p>Please follow up accordingly.</p>
</body>
</html>
