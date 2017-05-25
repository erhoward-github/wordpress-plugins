<?php
/*
Plugin Name: psgcnj-current-contacts-widget.php
Plugin URI:  https://psgcnj.biz/talentwp/
Description: Allow users to view and download active, prospective and retired (active) contacts
Version:     1.0
Author:      Elaine Howard
Author URI:  http://www.elainehoward.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wporg
Domain Path: /languages
*/

class psgcnj_current_contacts_widget extends WP_Widget
{
    function __construct()
    {
        parent::__construct(false, $name = __('PSGCNJ Current Contacts Widget'));
    }
    
    function form()
    {
        
    }
    
    function update()
    {
        
    }

    function query_current_contacts_data($conn, &$result)
    {
        
    }
    
    private $statusTypeActive = '';
    private $statusTypeProspective = '';
    private $statusTypeLandedActive = '';
    private $statusTypeTraining = '';
    private $statusTypeInactiveActive = '';
    private $statusTypeInactiveProspective = '';
    private $statusTypeLandedProspective = '';
    private $statusTypeRetiredPSG = '';
    private $statusTypeDropped = '';
    private $statusTypeVisitor = '';
    private $statusTypeBaddAddress = '';
    
    
    
    private $dataFile = 'psgcnj-data/current-contacts.csv';
    private $loginFile = 'psgcnj-includes/login-info.php';
    private $basePath = 'https://psgcnj.biz/talentwp/';
    
    
    function build_current_contacts_form_select()
    {
        $checkedStatusTypeActive = isset($_POST['statusTypeActive']) ? 'checked="checked"' : '';
        $checkedStatusTypeProspective = isset($_POST['statusTypeProspective']) ? 'checked="checked"' : '';
        $checkedStatusTypeRetiredPSG = isset($_POST['statusTypeRetiredPSG']) ? 'checked="checked"' : '';
        
        $html = <<< "__DOC__"
<form class="form-horizontal" action="" method="POST">

  <div class="form-group">
    <label for="inputEmail3" class="col-sm-2 control-label">Status Types</label>
    <div class="col-sm-10">
    
<select class="form-control" multiple="multiple" id="statusTypes" name="statusTypes[]" size="11">
<option value="1">Active</option>
<option value="3">Prospective</option>
<option value="4">Landed (Active)</option>
<option value="6">Training</option>
<option value="10">Inactive (Active)</option>
<option value="11">Inactive (Prospective)</option>
<option value="12">Landed (Prospective)</option>
<option value="15">Retired PSG</option>
<option value="16">Dropped</option>
<option value="17">Visitor</option>
<option value="18">Bad Address</option>
</select>
      
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
    }

    
    function build_current_contacts_form_checkboxes()
    {
        $checkedStatusTypeActive = isset($_POST['statusTypeActive']) ? 'checked="checked"' : '';
        $checkedStatusTypeProspective = isset($_POST['statusTypeProspective']) ? 'checked="checked"' : '';
        $checkedStatusTypeRetiredPSG = isset($_POST['statusTypeRetiredPSG']) ? 'checked="checked"' : '';
        
        $html = <<< "__DOC__"
<form action="" method="POST">
<div class="form-group">
<fieldset>
<legend>Status Type</legend>

<select class="form-control" multiple="multiple" id="statusTypes" name="statusTypes[]" size="11">
<option value="1">Active</option>
<option value="3">Prospective</option>
<option value="4">Landed (Active)</option>
<option value="6">Training</option>
<option value="10">Inactive (Active)</option>
<option value="11">Inactive (Prospective)</option>
<option value="12">Landed (Prospective)</option>
<option value="15">Retired PSG</option>
<option value="16">Dropped</option>
<option value="17">Visitor</option>
<option value="18">Bad Address</option>
</select>

<!--
<label for="statusTypeActive" class="checkbox-inline">
<input type="checkbox" 
    name="statusTypeActive" 
    id="statusTypeActive" 
    value="1" 
     {$checkedStatusTypeActive}>
<span>Active</span>
</label>

<label for="statusTypeProspective" class="checkbox-inline">
<input type="checkbox" name="statusTypeProspective" id="statusTypeProspective" value="3" {$checkedStatusTypeProspective}>
<span>Prospective</span>
</label>

<label for="statusTypeRetiredPSG" class="checkbox-inline">
<input type="checkbox" name="statusTypeRetiredPSG" id="statusTypeRetiredPSG" value="15" {$checkedStatusTypeRetiredPSG}>
<span>Retired PSG</span>
</label>
-->

</fieldset>
</div>
<div class="form-group">
<button type="submit" class="btn btn-primary">Search</button>
</div>
</form>
__DOC__;
        return $html;
    } # end build_current_contacts_form_checkboxes
    
    function build_current_contacts_html(&$result)
    {
        $html = <<< "__DOC__"
<div class="table-responsive">        
<table class="table table-striped table-bordered table-hover table-condensed">
<caption>Current Contacts</caption>
<thead><tr>
<th>Last Name</th>
<th>First Name</th>
<th>Address</th>
<th>City</th>
<th>State</th>
<th>Zip Code</th>
<th>Email</th>
<th>Status Type</th>
<th>Joined Date</th>
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
                $row[5] . '</td><td><div style="width:84px;word-wrap: break-word;">' . 
                '<a href="mailto:' . $row[6] . '">' . $row[6] . '</a></div></td><td>' . 
                $row[7] . '</td><td>' . 
                $row[8] . '</td></tr>';
        }
        $html .= '</tbody></table></div>';
        return $html;
    } # function build_current_contacts_html(&$result)
    
    function build_current_contacts_csv(&$result)
    {
        $file_name = $this->dataFile;
        $csv = <<< "__DOC__"
Last Name,First Name,Address,City,State,Zip Code,Email,Status Type,Joined Date\r\n
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
                $row[7] . ',' . 
                $row[8] . "\r\n";
        }
        $file_stream = fopen($file_name, 'w');
        $number_of_bytes = fwrite($file_stream, $csv);
        fclose($file_stream);
        return $csv;
    } # function build_current_contacts_csv($result)
    
    function widget($args, $instance)
    {
        $message = $this->build_current_contacts_form_select();
        

        if($_SERVER['REQUEST_METHOD'] == 'GET')
        {
        }
        else
        {
            require($this->loginFile);

            $isOK = TRUE;
            $result = NULL;
            $sql = '';
            $numRows = 0;
            $row = NULL;
            #$message = '';
            $conn = NULL;

            if($isOK)
            {
                $conn = @mysqli_connect($host, $usr, $pwd, $db);
                if(!$conn)
                {
                    $isOK = FALSE;
                    $message = '<div class="alert alert-warning">' . mysqli_connect_error() . '</div>';
                }
                else
                {
                    #$message = '';
                    $csv = '';

                    ###################################################################################
                    # current contacts
                    ###################################################################################
                    /*
                    $this->statusTypeActive       = $_POST['statusTypeActive'];
                    $this->statusTypeProspective  = $_POST['statusTypeProspective'];
                    $this->statusTypeRetiredPSG   = $_POST['statusTypeRetiredPSG'];
                    # $statusTypeLandedActive = $_POST['statusTypeLandedActive'];

                    $statusTypeList .= isset($this->statusTypeActive) ?  $this->statusTypeActive . ',' : '';
                    $statusTypeList .= isset($this->statusTypeProspective) ?  $this->statusTypeProspective . ',' : '';
                    $statusTypeList .= isset($this->statusTypeRetiredPSG) ?  $this->statusTypeRetiredPSG. ',' : '';
                    # $statusTypeList .= isset($this->statusTypeLandedActive) ?  $this->statusTypeLandedActive . ',' : '';
                    */
                    
                    $this->statusTypes = isset($_POST['statusTypes']) ? $_POST['statusTypes']: array();
                    $statusTypeList = '';
                    foreach($this->statusTypes as $statusType)
                    {
                        $statusTypeList .= $statusType . ',';
                    }
                    

                    $statusTypeList .= rtrim($statusTypeList, ',');
                    $statusTypeClause = '';
                    if(strlen($statusTypeList))
                    {
                        $statusTypeClause = " AND `Status Type ID` IN ({$statusTypeList})";
                    }
                    else
                    {
                        $statusTypeClause = " AND `Status Type ID` = -1";
                    }


                    $sql = <<< "__DOC__"
SELECT DISTINCT 
LastName AS `Last Name`, 
FirstName AS `First Name`, 
Address, 
City, 
StateOrProvince AS `State`, 
PostalCode AS `Postal Code`, 
EmailName AS Email, 
(SELECT `Status Type` FROM `Status Types` WHERE `Status Types`.`status type id` = c.`Status Type ID`) AS `Status Type`,
DATE_FORMAT(`PSG Joined Date`, '%d-%b-%Y') AS `PSG Joined Date` 
FROM Contacts AS c 
WHERE Address IS NOT NULL 
    AND City IS NOT NULL 
    AND StateOrProvince IS NOT NULL 
    AND EmailName IS NOT NULL 
    {$statusTypeClause}
ORDER BY LastName ASC, FirstName ASC, `Status Type ID` ASC
__DOC__;

# echo "<pre>{$sql}</pre>";

                    $result = mysqli_query($conn, $sql);
                    if($result)
                    {
                        $num_rows = mysqli_num_rows($result);
                        if($num_rows)
                        {
                            $message .= '<div class="alert alert-success" role="alert">Number of records: ' . $num_rows . '</div>';
                            $message .= 
                                "<p><a href='" . $this->basePath . $this->dataFile . "'>Download PSGCNJ current contacts file</a></p>";
                            $message .= $this->build_current_contacts_html($result);
                            $csv = $this->build_current_contacts_csv($result);
                            $message .= 
                                "<p><a href='" . $this->basePath . $this->dataFile . "'>Download PSGCNJ current contacts file</a></p>";
                        }
                        else
                        {
                            $message .= '<div class="alert alert-info">No records found</div>';
                        }
                        mysqli_free_result($result);
                    }
                    else
                    {
                        $message = '<div class="alert alert-warning">Error: ' . mysqli_error($conn) . '</div>';
                    }
                    
                    mysqli_close($conn);
                } // end if(!$conn)
            } # end if($isOK)
        } # end if($_SERVER['REQUEST_METHOD'] == 'GET')

        $html = <<< "__DOC__"
<section class="widget widget-members" id="current-contacts-1">
$message
</section>
__DOC__;
        echo $html;
    }
}

add_action("widgets_init",function(){
    register_widget("psgcnj_current_contacts_widget");
});
?>
