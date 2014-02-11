<?php
// We're making a call using the Services_Twilio library and 
// using the twilio credentials for this VBX instalation
$account = OpenVBX::getAccount();

// Set a limit of items per page
$limit = 50;

// Get the page of calls from Twilio
$calls = $account->calls->getPage(0, $limit, array())->getItems();

/**
 * Humanize strings
 *
 * @param string $status 
 * @param string $sep 
 * @return string
 */
function humanize($status, $sep = '-') 
{
    return ucwords(str_replace($sep, ' ', $status));
}

/**
 * Format a dialed object based on its type
 * $number will start with 'client:' if it was
 * a call made/answered with the browser phone
 *
 * @param string $number 
 * @return string
 */
function number_text($number) 
{
    if (preg_match('|^client:|', $number))
    {
        $user_id = str_replace('client:', '', $number);
        $user = VBX_User::get(array('id' => $user_id));
        $ret = $user->first_name.' '.$user->last_name.' (client)';
    }
    else
    {
        $ret = format_phone($number);
    }
    return $ret;
}

/**
 * Output a human readable date
 *
 * @param string $date 
 * @return string
 */
function format_date($date)
{
    $timestamp = strtotime($date);
    $date_string = date('M j, Y', $timestamp).'<br />'
                    .date('H:i:s T', $timestamp);
    return $date_string;
}

/**
 * Output a more clear direction
 *
 * @param string $direction
 * @return string
 */
function format_direction($direction)
{
    switch ($direction) {
	case 'inbound':
	    $direction = 'Inbound';
	    break;
	case 'outbound-api':
	    $direction = 'Outbound';
	    break;
	case 'outbound-dial':
	    $direction = 'Manual';
	    break;
    }

    return $direction;
}

?>

<div class="vbx-plugin">
    <h3>Call Log</h3>
    <p>Showing the last <?php echo $limit; ?> calls.</p>
    <table style="font-size: px;">
        <thead>
            <tr>
                <th>Type</th>
                <th>From</th>
                <th>To</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Duration</th>
                <th>Status</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($calls as $i => $call): ?>
            <tr>
                <td><?php echo format_direction($call->direction); ?></td>
                <td><?php echo number_text($call->from); ?></td>
                <td><?php echo number_text($call->to); ?></td>
                <td><?php echo format_date($call->start_time); ?></td>
                <td><?php echo format_date($call->end_time); ?></td>
                <td><?php echo $call->duration; ?> sec</td>
                <td><?php echo humanize($call->status); ?></td>
                <td><?php printf("%s %.2f", $call->price_unit, str_replace('-', '', $call->price)); ?></td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>
