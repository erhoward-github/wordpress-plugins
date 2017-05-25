<?php
/*
Plugin Name: psgcnj-landed-contacts-widget.php
Plugin URI:  https://psgcnj.biz/talentwp/
Description: Allow users to view and download landed contacts
Version:     1.0
Author:      Elaine Howard
Author URI:  http://www.elainehoward.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wporg
Domain Path: /languages
*/

class psgcnj_landed_contacts_widget extends WP_Widget
{
    function __construct()
    {
        parent::__construct(false, $name = __('PSGCNJ Landed Contacts Widget'));
    }
    
    function form()
    {
        
    }
    
    function update()
    {
        
    }

    function query_landed_contacts_data($conn, &$result)
    {
        
    }
    
    private $startDate = '';
    private $endDate = '';
    private $atMost = '';
    private $dataFile = 'psgcnj-data/landed-contacts.csv';
    private $loginFile = 'psgcnj-includes/login-info.php';
    private $basePath = 'https://psgcnj.biz/talentwp/';

    function build_landed_contacts_form()
    {
        $stickyStartDate = isset($_POST['startDate']) ? 'value="' . $_POST['startDate'] . '"' : '';
        $stickyEndDate = isset($_POST['endDate']) ? 'value="' . $_POST['endDate'] . '"' : '';
        $stickyAtMost = isset($_POST['atMost']) ? 'value="' . $_POST['atMost'] . '"' : '';
        
        $html = <<< "__DOC__"
<form class="form-horizontal" action="" method="POST">
<div class="form-group">
<label for="startDate" class="col-sm-2 control-label">Start Date</label>
<div class="col-sm-10">
<input type="text" class="form-control" id="startDate" name="startDate" placeholder="mm/dd/yyyy" autocomplete="off" {$stickyStartDate}>
</div>
</div>

<div class="form-group">
<label for="endDate" class="col-sm-2 control-label">End Date</label>
<div class="col-sm-10">
<input type="text" class="form-control" id="endDate" name="endDate" placeholder="mm/dd/yyyy" autocomplete="off" {$stickyEndDate}>
</div>
</div>

<div class="form-group">
<label for="atMost" class="col-sm-2 control-label">At Most</label>
<div class="col-sm-10">
<input type="number" class="form-control" id="atMost" name="atMost" maxlength="4" size="4" {$stickyAtMost}>
</div>
</div>

<div class="form-group">
<div class="col-sm-offset-2 col-sm-10">
<button type="submit" class="btn btn-primary">Search</button>
</div>
</div>

</form>
__DOC__;
        return $html;
    } # end function build_landed_contacts_form()
    
    
    function build_landed_contacts_html(&$result)
    {
        $html = <<< "__DOC__"
<div class="table-responsive">
<table class="table table-striped table-bordered table-hover table-condensed">
<caption>Landed Contacts</caption>
<thead><tr>
<th>Landed Date</th>
<th>Last Name</th>
<th>First Name</th>
<th>Address</th>
<th>City</th>
<th>State</th>
<th>Zip Code</th>
<th>Email</th>
</tr></thead><tbody>
__DOC__;
        while($row = mysqli_fetch_array($result, MYSQLI_NUM))
        {
            $html .= '<tr><td>' . 
                $row[0] . '</td><td>' . 
                $row[1] . '</td><td>' . 
                $row[2] . '</td><td>' . 
                $row[3] . '</td><td>' . 
                $row[4] . '</td><td>' . 
                $row[5] . '</td><td>' . 
                $row[6] . '</td><td>' . 
                '<a href="mailto:' . $row[7] . '">' . $row[7] . '</a></td></tr>';
        }
        $html .= '</tbody></table></div>';
        return $html;
    } # function build_landed_contacts_html(&$result)
    
    function build_landed_contacts_csv(&$result)
    {
        $file_name = $this->dataFile;
        $csv = <<< "__DOC__"
Landed Date,Last Name,First Name,Address,City,State,Zip Code,Email\r\n
__DOC__;
        mysqli_data_seek($result, 0);
        while($row = mysqli_fetch_array($result, MYSQLI_NUM))
        {
            $csv .= 
                $row[0] . ',' . 
                $row[1] . ',' . 
                $row[2] . ',' . 
                $row[3] . ',' . 
                $row[4] . ',' . 
                $row[5] . ',' . 
                $row[6] . ',' . 
                $row[7] . "\r\n";
        }
        $file_stream = fopen($file_name, 'w');
        $number_of_bytes = fwrite($file_stream, $csv);
        fclose($file_stream);
        return $csv;
    } # function build_landed_contacts_csv($result)
    
    function widget($args, $instance)
    {
        
   
        if($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $message .= $this->build_landed_contacts_form();     
        }
        else
        {
            require($this->loginFile);

            $isOK = TRUE;
            $result = NULL;
            $sql = '';
            $numRows = 0;
            $row = NULL;
            $conn = NULL;
            $csv = '';
            $dataEntryErrors = array();

            $conn = @mysqli_connect($host, $usr, $pwd, $db);
            if(!$conn)
            {
                $isOK = FALSE;
                $message = '<div class="alert alert-warning">' . mysqli_connect_error() . '</div>';
            }
            
            if($isOK)
            {
                $startDateUser = mysqli_real_escape_string($conn, strip_tags(trim($_POST['startDate'])));
                if(strlen($startDateUser))
                {
                    $matchFound = preg_match('/^\d\d\/\d\d\/\d\d\d\d$/',$startDateUser) == 1 ? TRUE : FALSE;
                    if($matchFound)
                    {
                        list($startDateMonth, $startDateDay, $startDateYear) = split('[/.-]', $startDateUser);
                        if(checkdate($startDateMonth, $startDateDay, $startDateYear))
                        {
                            $isOk = TRUE;
                            $this->startDate = $startDateYear . '-' . $startDateMonth . '-' . $startDateDay;
                        }
                        else
                        {
                            $isOK = FALSE;
                            $dataEntryErrors[] = 'Enter a valid start date';
                        }
                    }
                    else
                    {
                        $isOK = FALSE;
                        $dataEntryErrors[] = 'Enter the start date in the proper format: mm/dd/yyyy';
                    }
                }
                
                $endDateUser = mysqli_real_escape_string($conn, strip_tags(trim($_POST['endDate'])));
                if(strlen($endDateUser))
                {
                    $matchFound = preg_match('/^\d\d\/\d\d\/\d\d\d\d$/',$endDateUser) == 1 ? TRUE : FALSE;
                    if($matchFound)
                    {
                        list($endDateMonth, $endDateDay, $endDateYear) = split('[/.-]', $endDateUser);
                        if(checkdate($endDateMonth, $endDateDay, $endDateYear))
                        {
                            $isOk = TRUE;
                            $this->endDate = $endDateYear . '-' . $endDateMonth . '-' . $endDateDay;
                        }
                        else
                        {
                            $isOK = FALSE;
                            $dataEntryErrors[] = 'Enter a valid end date';
                        }
                    }
                    else
                    {
                        $isOK = FALSE;
                        $dataEntryErrors[] = 'Enter the end date in the proper format: mm/dd/yyyy';
                    }
                }
                if($isOK)
                {
                    $startDateClause = $this->startDate ? ("LandedDate >= '" . $this->startDate . "' ") : '';
                    $endDateClause = $this->endDate ? ("LandedDate <= '" . $this->endDate . "' ") : '';
                    if(strlen($startDateClause))
                    {
                        if(strlen($endDateClause))
                        {
                            $dateRangeClause = ' AND (' . $startDateClause . ' AND '. $endDateClause. ') ';
                        }
                        else
                        {
                            $dateRangeClause = ' AND ' . $startDateClause . ' ';
                        }
                    }
                    else
                    {
                        if(strlen($endDateClause))
                        {
                            $dateRangeClause = ' AND '.  $endDateClause . ' ';
                        }
                        else
                        {
                            $dateRangeClause = ' AND LandedDate IS NOT NULL ';
                        }
                    }
                }
                
                $this->atMost = 0;
                if(empty($_POST['atMost']))
                {
                    $this->atMost = 0;
                }
                else if(is_numeric($_POST['atMost']))
                {
                    $this->atMost = (int) $_POST['atMost'];
                    if($this->atMost <= 0)
                    {
                        $this->atMost = 0;
                        $isOK = FALSE;
                        $dataEntryErrors[] = 'Enter a positive number';
                    }
                }
                else
                {
                    $this->atMost = 0;
                    $isOK = FALSE;
                    $dataEntryErrors[] = 'Enter a number';
                }
                if($isOK)
                {
                    $atMostClause = ($this->atMost > 0) ? ('LIMIT ' . $this->atMost) : '';
                }
                
                
                if(!$isOK)
                {
                    $message .= '<div class="alert alert-warning"><ul>';
                    foreach($dataEntryErrors as $dataEntryError)
                    {
                        $message .= '<li>' . $dataEntryError . '</li>';
                    }
                    $message .= '</ul></div>';
                    $message .= $this->build_landed_contacts_form();     
                    
                }
                if($isOK)
                {
                    $sql = <<< "__DOC__"
SELECT DISTINCT 
DATE_FORMAT(LandedDate, '%d-%b-%Y') AS `Landed Date`,
LastName AS `Last Name`, 
FirstName AS `First Name`, 
Address, 
City, 
StateOrProvince AS `State`, 
PostalCode AS `Postal Code`, 
EmailName AS Email
FROM Contacts
WHERE 1 = 1
    {$dateRangeClause}
    AND `Status Type ID` IN ('4','12') 
ORDER BY LandedDate DESC, LastName ASC, FirstName ASC 
{$atMostClause}
__DOC__;

/*
echo '<pre>' . $startDate . '</pre>';
echo '<pre>' . $endDate . '</pre>';
echo '<pre>' . $sql . '</pre>';
*/

                    $result = mysqli_query($conn, $sql);
                    if($result)
                    {
                        $message .= $this->build_landed_contacts_form();     
                        
                        $num_rows = mysqli_num_rows($result);
                        if($num_rows)
                        {
                            $message .= '<div class="alert alert-success" role="alert">Number of records: ' . $num_rows . '</div>';
                            $message .= 
                                "<p><a href='" . $this->basePath . $this->dataFile . "'>Download PSGCNJ landed contacts file</a></p>";
                            $message .= $this->build_landed_contacts_html($result, $num_rows);
                            $csv = $this->build_landed_contacts_csv($result);
                            $message .= 
                                "<p><a href='" . $this->basePath . $this->dataFile . "'>Download PSGCNJ landed contacts file</a></p>";
                        }
                        else
                        {
                            $message .= '<div class="alert alert-info">No records found</div>';
                        }
                        mysqli_free_result($result);
                    }
                    else
                    {
                        $message .= '<div class="alert alert-danger">' . mysqli_error($conn) . '</div>';
                    }    

                    
                    mysqli_close($conn);
                    
                }
                
            }
        }
        $html = <<< "DOC"
<section class="widget widget-members" id="landed-contacts-1">
$message
</section>
<script>
jQuery(function() {
    jQuery("#startDate").datepicker({dateFormat:"mm/dd/yy"});
    jQuery("#endDate").datepicker({dateFormat:"mm/dd/yy"});
});
</script>
DOC;
        echo $html;
    }
}

add_action("widgets_init",function(){
    register_widget("psgcnj_landed_contacts_widget");
});
?>
