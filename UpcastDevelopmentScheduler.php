<?php

/** This class is responsible for creating a periodical CSV file with the meeting details for the Upcast's development team
 * 
 * @author Georgi Zhivankin <georgijivankin@gmail.com>
 * @Version 1.0
 * @Since 1.0
 * */
class UpcastDevelopmentScheduler
{

    /**
     * Construct the class
     *
     * @param string $localTimezone            
     */
    public function __construct($defaultTimezone = 'UTC')
    {
        // Initialize the script by setting the correct timezone first
        date_default_timezone_set($defaultTimezone);
    }

    /**
     * Check if the supplied date falls on a weekend.
     * If the $includesFriday parameter is set to true, the weekend should also include Friday.
     *
     * @param string $date            
     * @param bool $includesFriday            
     * @return bull
     */
    public function isWeekend($date, $includesFriday = false)
    {
        // Construct a new DateTime object
        try {
            $date = new DateTime($date);
            // Format the date as a number from 0 to 7, corresponding to the number of days in the week
            $dateNumber = $date->format('N');
            // First, check if it is a non-standard weekend, I.E. $includesFriday = true
            if ($includesFriday === true) {
                // Check if the date falls on Friday, Saturday or Sunday, I.E. it is an extended weekend
                if ($dateNumber >= 5) {
                    // The day is part of the weekend, so return true
                    return true;
                } else {
                    // The day is not part of the weekend, so return false
                    return false;
                }
                // Or check if it is a proper weekend, I.E. $includesFriday = false
            } elseif ($includesFriday === false) {
                // Check if the date falls on Saturday or Sunday only
                if ($dateNumber >= 6) {
                    // The day is either a Saturday or Sunday, so return true
                    return true;
                } else {
                    // The day is neither a Saturday, nor a Sunday, so return false
                    return false;
                }
            }
        } catch (Exception $e) {
            echo 'Exception: ', $e->getMessage(), "\n";
        }
        
    }

    /**
     * Determine Mid Month Meeting Date
     * If date is the 14th and it falls on Saturday or Sunday, recalculate it so that the date becomes the first Monday after the weekend. The method can work with any date supplied in the ('YYYY-mm-dd') format, it could be 14, 16, 20, etc.
     *
     * @param string $date            
     * @return Exception|DateTime
     */
    public function determineMidMonthMeetingDate($date)
    {
        // Construct a new DateTime object
        try {
            $date = new DateTime($date);
            // Check if the supplied date falls on a weekend
            if (self::isWeekend($date->format('Y-m-d'))) {
                // If it is Sunday, Get the monday of the same week as the week begins on Sunday by default in PHP's date functions, else, get the Monday of next week
                $monday = clone $date->modify(('Sunday' == $date->format('l')) ? 'Monday this week' : 'Monday next week');
                // Return the monday after the weekend
                return $monday;
            } else {
                // Return the original date as it does not fall on a weekend
                return $date;
            }
        } catch (Exception $e) {
            echo 'Exception: ', $e->getMessage(), "\n";
        }
        
    }

    /**
     * determineEndOfMonthTestingDate
     * The testing date is the last date of the month, but if it falls on Friday, Saturday or Sunday, it should be recalculated so that it falls on the Thursday before that weekend. The method can work with any date of the month in the ('YYYY-mm-dd') format, but it is preferable to give it the first date of the month.
     *
     * @param string $date            
     * @return Exception|DateTime
     */
    public function determineEndOfMonthTestingDate($date)
    {
        // Construct a new DateTime object
        try {
            $date = new DateTime($date);
            // Get the last day of the month
            $lastDayOfMonth = $date->modify(sprintf('+%d days', $date->format('t') - $date->format('j')));
            // echo "Last day of the month : " . $lastDayOfMonth->format('l d.m.Y') . "<br/>";
            // Check if the date falls on a Friday, Saturday or Sunday
            if (Self::isWeekend($lastDayOfMonth->format('Y-m-d'), true)) {
                // Get the last Thursday of the previous week. If the date is a Sunday, the Thursday is from previous week, but if it is not, Thursday is from the same week as PHP begins each week from Sunday.
                $thursday = clone $lastDayOfMonth->modify(('Sunday' == $date->format('l')) ? 'Thursday last week' : 'Thursday this week');
                // Return the Thursday before the weekend
                return $thursday;
            } else {
                // Return the last day of the month
                return $lastDayOfMonth;
            }
        } catch (Exception $e) {
            echo 'Exception: ', $e->getMessage(), "\n";
        }
        
    }

    /**
     * Write data to file
     * Generate data for all supplied months and write the data into a CSV file through the native fputcsv PHP functions
     *
     * @param string $date            
     * @param string $fileName            
     * @param int $period            
     * @return bool
     */
    public function writeDataToFile($date = null, $fileName = NULL, $period = null)
    {
        // Check whether the parameters were supplied and if not, set sensible defaults
        if (is_null($date)) {
            $date = (new DateTime())->format('Y-m-d');
        }
        if (is_null($fileName)) {
            $fileName = date('ymd') . '_Upcast_Monthly_Schedule.csv';
        }
        if (is_null($period)) {
            $period = 6;
        }
        // Construct a new DateTime object
        try {
            $date = new DateTime($date);
            // Open a file for writing in the same directory where this script is located
            $fb = fopen($fileName, 'w');
            echo "Starting Export...\n\n";
            // Create a header row
            $headerRow = array(
                'Month',
                'Mid Month Meeting Date',
                'End of Month Testing Date'
            );
            // Output the header row to standard output
            echo implode("\t", $headerRow);
            echo "\n";
            // Put the header row into the file
            fputcsv($fb, $headerRow, ",", "\"");
            
            // Get current month from the supplied date
            $currentMonth = $date->format('m');
            // Go through the period given and generate the data for the subsequent months by adding as many months as the set period, starting from the supplied month onwards
            for ($i = 0; $i < $period; $i ++) {
                /** Get a timestamp of the given month as it is easier to generate the data that way
                                  * $monthTimestamp = strtotime('+' . $i . ' months', strtotime(date('Y-M' . '-01')));
                                  */
                // Generate the month based on the period and format it accordingly
                $generatedMonth = (new DateTime($date->format('Y') . '-' . $date->format('m') . '-' . $date->format('d')))->modify('+' . $i . ' months');
                // Extract day, month and year variables from the generated date
                $day = $generatedMonth->format('d');
                $month = $generatedMonth->format('m');
                $monthNameAndYear = $generatedMonth->format('F Y');
                $year = $generatedMonth->format('Y');
                // Generate the two dates for the actual export by executing the related methods with the generated month
                $midMonthMeetingDate = self::determineMidMonthMeetingDate($year . '-' . $month . '-14')->format('l, d.m.Y');
                $endOfMonthTestingDate = Self::determineEndOfMonthTestingDate($year . '-' . $month . '-1')->format('l, d.m.Y');
                // Add the whole row into an array in order to pass it onto the fputcsv function
                $row = array(
                    $monthNameAndYear,
                    $midMonthMeetingDate,
                    $endOfMonthTestingDate
                );
                // Write the data into the file
                fputcsv($fb, $row, ",", "\"");
                // Output the data to standard output
                echo $monthNameAndYear . "\t" . $midMonthMeetingDate . "\t" . $endOfMonthTestingDate . "\n";
            }
            // Put a blank line at the end of the records
            echo "\n";
            // Close the file
            fclose($fb);
            echo "File exported successfully.\n";
        } catch (Exception $e) {
            echo 'Exception: ', $e->getMessage(), "\n";
        }
        
    }
    
}
    
    // Initialize the class by fetching a few variables from the command line input first. They are optional though and may be excluded.
$date = (isset($argv[1])) ? ($date = $argv[1]) : ($date = null);
$fileName = (isset($argv[2])) ? ($fileName = $argv[2]) : ($fileName = null);
$period = (isset($argv[3])) ? ($period = $argv[3]) : ($period = null);

// Sanitize the parameters by removing apostrophes (if any)

if (! is_null($date)) {
    $date = str_replace('\'', '', $date);
}

if (! is_null($fileName)) {
    $fileName = str_replace('\'', '', $fileName);
}

if (! is_null($period)) {
    $period = str_replace('\'', '', $period);
    // Check if anything remains after removing the apostrophes
    if (! is_numeric($period)) {
        // Set the argument to null
        $period = null;
    }
}

// Instantiate the class in the UTC timezone
$upcastDevelopmentScheduler = new UpcastDevelopmentScheduler('UTC');
// Generate the data
$upcastDevelopmentScheduler->writeDataToFile($date, $fileName, $period);