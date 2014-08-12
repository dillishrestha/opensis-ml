<?php
#**************************************************************************
#  openSIS is a free student information system for public and non-public 
#  schools from Open Solutions for Education, Inc. web: www.os4ed.com
#
#  openSIS is  web-based, open source, and comes packed with features that 
#  include student demographic info, scheduling, grade book, attendance, 
#  report cards, eligibility, transcripts, parent portal, 
#  student portal and more.   
#
#  Visit the openSIS web site at http://www.opensis.com to learn more.
#  If you have question regarding this system or the license, please send 
#  an email to info@os4ed.com.
#
#  This program is released under the terms of the GNU General Public License as  
#  published by the Free Software Foundation, version 2 of the License. 
#  See license.txt.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#***************************************************************************************
include_once("i18n.php");
include('../../Redirect_modules.php');
DrawBC(_('Users')." > ".ProgramTitle());

if(clean_param($_REQUEST['values'],PARAM_NOTAGS) && ($_POST['values'] || $_REQUEST['ajax']))
{
	if(clean_param($_REQUEST['tab'],PARAM_ALPHAMOD)=='password')
	{
            $column_name= PASSWORD;
            $pass_current= paramlib_validation($column_name,$_REQUEST['values']['current']);
            $pass_new= paramlib_validation($column_name,$_REQUEST['values']['new']);
            $pass_verify= paramlib_validation($column_name,$_REQUEST['values']['verify']);
		
		
            $pass_new_after= md5($pass_new);
		
            $profile_RET = DBGet(DBQuery('SELECT s.PROFILE FROM staff s , staff_school_relationship ssr WHERE s.STAFF_ID=ssr.STAFF_ID AND s.STAFF_ID=\''.User('STAFF_ID').'\' AND ssr.SYEAR=\''.UserSyear().'\''));
           
            
            
           
            $sql=DBQuery('SELECT PASSWORD FROM staff s , staff_school_relationship ssr where s.password=\''.$pass_new_after.'\'  AND ssr.SYEAR=\''.UserSyear().'\'');
            $number=mysql_num_rows($sql);            
                       
		if($pass_new != $pass_verify)
			$error =''._('Your new passwords did not match').'';
               
                elseif($number>0)
                {
                   echo '<font color = red><b>'._('This password is alredy taken').'</b></font>';
                }
               
		else
		{
			$password_RET = DBGet(DBQuery('SELECT PASSWORD FROM staff s , staff_school_relationship ssr WHERE s.STAFF_ID AND ssr.STAFF_ID AND s.STAFF_ID=\''.User('STAFF_ID').'\' AND ssr.SYEAR=\''.UserSyear().'\''));
                        if($password_RET[1]['PASSWORD'] != md5($pass_current))
				$error = 'Your current password was incorrect.';
			else
			{
				DBQuery('UPDATE staff SET PASSWORD=\''.md5($pass_new).'\' WHERE STAFF_ID=\''.User('STAFF_ID').'\'');
				$note = ''._('Your new password was saved').'';
			}
		}
		
		
		/*
		if(strtolower($_REQUEST['values']['new'])!=strtolower($_REQUEST['values']['verify']))
			$error = 'Your new passwords did not match.';
		else
		{
			$password_RET = DBGet(DBQuery("SELECT PASSWORD FROM staff WHERE STAFF_ID='".User('STAFF_ID')."' AND SYEAR='".UserSyear()."'"));
			if(strtolower($password_RET[1]['PASSWORD'])!=md5(strtolower($_REQUEST['values']['current'])))
				$error = 'Your current password was incorrect.';
			else
			{
				DBQuery("UPDATE staff SET PASSWORD='".md5($_REQUEST['values']['new'])."' WHERE STAFF_ID='".User('STAFF_ID')."' AND SYEAR='".UserSyear()."'");
				$note = 'Your new password was saved.';
			}
		}
		*/
	}
	else
	{
		$current_RET = DBGet(DBQuery('SELECT TITLE,VALUE,PROGRAM FROM program_user_config WHERE USER_ID=\''.User('STAFF_ID').'\' AND PROGRAM IN (\''.'Preferences'.'\',\''.'StudentFieldsSearch'.'\',\''.'StudentFieldsView'.'\') '),array(),array('PROGRAM','TITLE'));

		if($_REQUEST['tab']=='student_listing' && $_REQUEST['values']['Preferences']['SEARCH']!='Y')
			$_REQUEST['values']['Preferences']['SEARCH'] = 'N';
		if($_REQUEST['tab']=='student_listing' && $_REQUEST['values']['Preferences']['DEFAULT_FAMILIES']!='Y')
			$_REQUEST['values']['Preferences']['DEFAULT_FAMILIES'] = 'N';
		if($_REQUEST['tab']=='student_listing' && $_REQUEST['values']['Preferences']['DEFAULT_ALL_SCHOOLS']!='Y')
			$_REQUEST['values']['Preferences']['DEFAULT_ALL_SCHOOLS'] = 'N';
		if($_REQUEST['tab']=='display_options' && $_REQUEST['values']['Preferences']['HIDDEN']!='Y')
			$_REQUEST['values']['Preferences']['HIDDEN'] = 'N';
		if($_REQUEST['tab']=='display_options' && $_REQUEST['values']['Preferences']['HIDE_ALERTS']!='Y')
			$_REQUEST['values']['Preferences']['HIDE_ALERTS'] = 'N';
		if($_REQUEST['tab']=='display_options' && $_REQUEST['values']['Preferences']['THEME']!=$current_RET['Preferences']['THEME'][1]['VALUE'])
		{
			echo '<script language=JavaScript>';
			echo 'parent.side.location="'.$_SESSION['Side_PHP_SELF'].'?modcat="+parent.side.document.forms[0].modcat.value;';
			echo "parent.help.location='Bottom.php?modcat=Users&modname=$_REQUEST[modname]';";
			echo '</script>';
                        $flag=1;
		}
		if(clean_param($_REQUEST['tab'],PARAM_ALPHAMOD)=='student_fields')
		{
			DBQuery('DELETE FROM program_user_config WHERE USER_ID=\''.User('STAFF_ID').'\' AND PROGRAM IN (\''.'StudentFieldsSearch'.'\',\''.'StudentFieldsView'.'\')');

			foreach($_REQUEST['values'] as $program=>$values)
			{
				foreach($values as $name=>$value)
				{
					if(isset($value))
						DBQuery('INSERT INTO program_user_config (USER_ID,PROGRAM,TITLE,VALUE) values(\''.User('STAFF_ID').'\',\''.$program.'\',\''.$name.'\',\''.$value.'\')');
				}
			}
		}
		else
		{
			foreach($_REQUEST['values'] as $program=>$values)
			{
				foreach($values as $name=>$value)
				{
					if(!$current_RET[$program][$name] && $value!='')
						DBQuery('INSERT INTO program_user_config (USER_ID,PROGRAM,TITLE,VALUE) values(\''.User('STAFF_ID').'\',\''.$program.'\',\''.$name.'\',\''.$value.'\')');
					elseif($value!='')
						DBQuery('UPDATE program_user_config SET VALUE=\''.$value.'\' WHERE USER_ID=\''.User('STAFF_ID').'\' AND PROGRAM=\''.$program.'\' AND TITLE=\''.$name.'\'');
					else
						DBQuery('DELETE FROM program_user_config WHERE USER_ID=\''.User('STAFF_ID').'\' AND PROGRAM=\''.$program.'\' AND TITLE=\''.$name.'\'');
				}
			}
		}
		// So Preferences() will get the new values
		unset($_openSIS['Preferences']);
	}
	unset($_REQUEST['values']);
	unset($_SESSION['_REQUEST_vars']['values']);
        if($flag==1)
            echo "<script>document.forms[0].submit();</script>";
        else 
            header("Location:Modules.php?modname=Users/Preferences.php");
	
}

unset($_REQUEST['search_modfunc']);
unset($_SESSION['_REQUEST_vars']['search_modfunc']);

if(!$_REQUEST['modfunc'])
{
	$current_RET = DBGet(DBQuery('SELECT TITLE,VALUE,PROGRAM FROM program_user_config WHERE USER_ID=\''.User('STAFF_ID').'\' AND PROGRAM IN (\''.'Preferences'.'\',\''.'StudentFieldsSearch'.'\',\''.'StudentFieldsView'.'\') '),array(),array('PROGRAM','TITLE'));

	if(!$_REQUEST['tab'])
		$_REQUEST['tab'] = 'display_options';

	echo "<FORM name=perf_form id=perf_form action=Modules.php?modname=$_REQUEST[modname]&amp;tab=$_REQUEST[tab] method=POST onload='document.forms[0].submit;'>";
	//DrawHeader('','<INPUT type=submit value=Save>');
	echo '<BR>';

	if(User('PROFILE')=='admin' || User('PROFILE')=='teacher')
		$tabs = array(array('title'=>''._('Display Options').'','link'=>"Modules.php?modname=$_REQUEST[modname]&amp;tab=display_options"),array('title'=>''._('Student Listing').'','link'=>"Modules.php?modname=$_REQUEST[modname]&amp;tab=student_listing"),array('title'=>''._('Password').'','link'=>"Modules.php?modname=$_REQUEST[modname]&amp;tab=password"),array('title'=>''._('Student Fields').'','link'=>"Modules.php?modname=$_REQUEST[modname]&amp;tab=student_fields"));
	else
		$tabs = array(array('title'=>''._('Display Options').'','link'=>"Modules.php?modname=$_REQUEST[modname]&amp;tab=display_options"),array('title'=>''._('Password').'','link'=>"Modules.php?modname=$_REQUEST[modname]&amp;tab=password"),array('title'=>''._('Student Fields').'','link'=>"Modules.php?modname=$_REQUEST[modname]&amp;tab=student_fields"));

	$_openSIS['selected_tab'] = "Modules.php?modname=$_REQUEST[modname]&amp;tab=".$_REQUEST['tab'];
	PopTable('header',$tabs);

	//echo '<fieldset>';

	if(clean_param($_REQUEST['tab'],PARAM_ALPHAMOD)=='student_listing')
	{
		echo '<table width=100% border=0></tr><td align=center><TABLE border=0>';
		echo '<TR><TD valign=top class=label_vtop>'._('Student Name').'</TD><TD><INPUT type=radio name=values[Preferences][NAME] value='._('Common').''.((Preferences('NAME')=='Common')?' CHECKED':'').'>'._('Common Name').'<BR><INPUT type=radio name=values[Preferences][NAME] value='._('Given').''.((Preferences('NAME')=='Given')?' CHECKED':'').'>'._('Given Name').'</TD></TR>';
		echo '<TR><TD valign=top class=label_vtop>'._('Student Sorting').'</TD><TD><INPUT type=radio name=values[Preferences][SORT] value='._('Name').''.((Preferences('SORT')=='Name')?' CHECKED':'').'>'._('Name').'<BR><INPUT type=radio name=values[Preferences][SORT] value='._('Grade').''.((Preferences('SORT')=='Grade')?' CHECKED':'').'>'._('Grade, Name').'</TD></TR>';
		echo '<TR><TD valign=top class=label_vtop>'._('File Export Type').'</TD><TD><INPUT type=radio name=values[Preferences][DELIMITER] value='._('Tab').''.((Preferences('DELIMITER')=='Tab')?' CHECKED':'').'>'._('Tab-Delimited (Excel)').'<BR><INPUT type=radio name=values[Preferences][DELIMITER] value=CSV'.((Preferences('DELIMITER')=='CSV')?' CHECKED':'').'>'._('CSV (OpenOffice)').'</TD></TR>';
		echo '<TR><TD><BR></TD><TD><BR></TD>';
		echo '<TR><TD colspan=2><INPUT type=checkbox name=values[Preferences][SEARCH] value=Y'.((Preferences('SEARCH')=='Y')?' CHECKED':'').'>'._('Display student search screen').'</TD></TR>';
		if(User('PROFILE')=='admin')
		{
			echo '<TR><TD colspan=2><INPUT type=checkbox name=values[Preferences][DEFAULT_FAMILIES] value=Y'.((Preferences('DEFAULT_FAMILIES')=='Y')?' CHECKED':'').'>'._('Group by family by default').'</TD></TR>';
			echo '<TR><TD colspan=2><INPUT type=checkbox name=values[Preferences][DEFAULT_ALL_SCHOOLS] value=Y'.((Preferences('DEFAULT_ALL_SCHOOLS')=='Y')?' CHECKED':'').'>'._('Search all schools by default').'</TD></TR>';
		}
		echo '</TABLE></td></tr></table>';
	}

	if(clean_param($_REQUEST['tab'],PARAM_ALPHAMOD)=='display_options')
	{
		echo '<TABLE align=center border=0 width=100%>';

		echo '<TR><TD><b>'._('Theme').'</b></TR></td><tr><td>';
		if($handle = opendir($openSISPath.'themes/'))
		{
			while(false !== ($file = readdir($handle)))
			{
				if($file != "." && $file != ".." && $file != "Black_rtl" && $file != "Blue_rtl" && $file != "Gray_rtl" && $file != "Green_rtl" && !in_array($file,$IgnoreFiles))
				{
                                
					echo '<INPUT type=radio name=values[Preferences][THEME] value='.$file.((Preferences('THEME')==$file)?' CHECKED':'').'>'.($file).'';
					$count++;
					
						
				}
			}
			closedir($handle);
			echo '</td></TR><TR><td class=break></td></tr><tr>';
		}

		$colors = array('#85e1ff','#96f3c8','#e9f396','#f3bb96','#f396a7');
		echo '<TR><TD><b>'._('Highlight Color').'</b></TD></tr><tr><TD><TABLE><TR>';
		foreach($colors as $color)
			echo '<TD bgcolor='.$color.'><INPUT type=radio name=values[Preferences][HIGHLIGHT] value='.$color.((Preferences('HIGHLIGHT')==$color)?' CHECKED':'').'></TD>';
		echo '</TR></TABLE></TD></TR><TR><td class=break></td></tr>';

//		echo '<TR><TD><b>Currency</b></TD></tr><tr><TD>';
//              #  echo '<TR><TD><b>Currency</b></TD></tr><tr><TD><SELECT name=values[Preferences][CURRENCY]>';
//		 $values = DBGet(DBQuery("SELECT  VALUE AS ID,TITLE FROM program_config WHERE PROGRAM='Currency' "));
//               # $values = array('USD' => 'US Dollar','GBP' => 'British Pound','EUR' => 'Euro','CAD' => 'Canadian Dollar','AUD' => 'Australian Dollar','JPY' => 'Japanese Yen','BRL' => 'Brazillian Real');
//		##foreach($values as $symbol => $name)
//			#echo '<OPTION value='.$symbol.((Preferences('CURRENCY')==$symbol)?' SELECTED':'').'>'.$name.'</OPTION>';
//		#echo '</SELECT></TD></TR>';
//               $current_RET = DBGet(DBQuery("SELECT TITLE,VALUE,PROGRAM FROM program_user_config WHERE USER_ID='1' AND TITLE='CURRENCY' AND PROGRAM IN ('Preferences') ") );
//               $val = $current_RET[1]['VALUE'];
//               foreach($values as $symbol)
//                $symbols[$symbol['ID']] = $symbol['TITLE'];
//                echo SelectInput($val,'values[Preferences][CURRENCY]','',$symbols,'N/A');
//                echo '</TD></TR>';

		echo '<TR><TD><b>'._("Date Format").'</b></TD></tr><tr><TD><SELECT name=values[Preferences][MONTH]>';
//		$values = array('F','M','m','n');
                                    $values = array('F','M','n');
		foreach($values as $value)
			echo '<OPTION value='.$value.((Preferences('MONTH')==$value)?' SELECTED':'').'>'.date($value).'</OPTION>';
		echo '</SELECT> ';
		echo '<SELECT name=values[Preferences][DAY]>';
//		$values = array('d','j','jS');
                                    $values = array('j','jS');
		foreach($values as $value)
			echo '<OPTION value='.$value.((Preferences('DAY')==$value)?' SELECTED':'').'>'.date($value).'</OPTION>';
		echo '</SELECT> ';
		echo '<SELECT name=values[Preferences][YEAR]>';
		$values = array('Y','y');
		foreach($values as $value)
			echo '<OPTION value="'.$value.'"'.((Preferences('YEAR')==$value)?' SELECTED':'').'>'.date($value).'</OPTION>';
		echo '</SELECT>';
		echo '</TD></TR>';
		echo '<TR><TD><INPUT type=checkbox name=values[Preferences][HIDE_ALERTS] value=Y'.((Preferences('HIDE_ALERTS')=='Y')?' CHECKED':'').'>'._('Disable login alerts').'</TD></TR>';
		echo '<TR><TD><INPUT type=checkbox name=values[Preferences][HIDDEN] value=Y'.((Preferences('HIDDEN')=='Y')?' CHECKED':'').'>'._('Display data using hidden fields').'</TD></TR>';
		echo '</TABLE>';
	}

	if(clean_param($_REQUEST['tab'],PARAM_ALPHAMOD)=='password')
	{
                echo '<div id=divErr style=display:none></div>';
		if($error)
			echo ErrorMessage(array($error));
		if($note)
			echo ErrorMessage(array($note),'note');
		echo '<table width=100% cellspacing=6 cellpadding2><tr><td align=center><TABLE><TR><TD align=right>'._('Current Password').'</TD><TD><INPUT type=password class=cell_floating name=values[current] AUTOCOMPLETE = off></TD></TR>
						<TR><TD align=right>'._('New Password').'</TD><TD><INPUT type=password id=new_pass class=cell_floating name=values[verify] AUTOCOMPLETE = off onkeyup=passwordStrength(this.value);passwordMatch();><span id=passwordStrength></span></TD></TR>
						<TR><TD align=right>'._('Verify New Password').'</TD><TD><INPUT type=password id=ver_pass class=cell_floating name=values[new] AUTOCOMPLETE = off onkeyup=passwordMatch()><span id=passwordMatch></span></TD></TR></TABLE></td></tr></table>';
	}

	if(clean_param($_REQUEST['tab'],PARAM_ALPHAMOD)=='student_fields')
	{
		if(User('PROFILE_ID'))
			$custom_fields_RET = DBGet(DBQuery('SELECT CONCAT(\''.'<b>'.'\',sfc.TITLE,\''.'</b>'.'\') AS CATEGORY,cf.ID,cf.TITLE,\''.''.'\' AS SEARCH,\''.''.'\' AS DISPLAY FROM custom_fields cf,student_field_categories sfc WHERE sfc.ID=cf.CATEGORY_ID AND (SELECT DISTINCT CAN_USE FROM profile_exceptions WHERE PROFILE_ID=\''.User('PROFILE_ID').'\' AND MODNAME=CONCAT(\''.'Students/Student.php&category_id='.'\',cf.CATEGORY_ID))=\''.'Y'.'\' ORDER BY sfc.SORT_ORDER,sfc.TITLE,cf.SORT_ORDER,cf.TITLE'),array('SEARCH'=>'_make','DISPLAY'=>'_make'),array('CATEGORY'));
		else
			$custom_fields_RET = DBGet(DBQuery('SELECT CONCAT(\''.'<b>'.'\',sfc.TITLE,\''.'</b>'.'\') AS CATEGORY,cf.ID,cf.TITLE,\''.''.'\' AS SEARCH,\''.''.'\' AS DISPLAY FROM custom_fields cf,student_field_categories sfc WHERE sfc.ID=cf.CATEGORY_ID AND (SELECT DISTINCT CAN_USE FROM staff_exceptions WHERE USER_ID=\''.User('STAFF_ID').'\' AND MODNAME=CONCAT(\''.'Students/Student.php&category_id='.'\',cf.CATEGORY_ID))=\''.'Y'.'\' ORDER BY sfc.SORT_ORDER,sfc.TITLE,cf.SORT_ORDER,cf.TITLE'),array('SEARCH'=>'_make','DISPLAY'=>'_make'),array('CATEGORY'));

		$THIS_RET['ID'] = 'CONTACT_INFO';
		$custom_fields_RET[-1][1] = array('CATEGORY'=>'<B>'._("Contact Information").'</B>','ID'=>'CONTACT_INFO','TITLE'=>'<IMG SRC=assets/down_phone_button.gif width=15> '._('Contact Info Rollover').'','DISPLAY'=>_make('','DISPLAY'));
		$THIS_RET['ID'] = 'HOME_PHONE';
		$custom_fields_RET[-1][] = array('CATEGORY'=>'<B>'._("Contact Information").'</B>','ID'=>'HOME_PHONE','TITLE'=>''._('Home Phone Number').'','DISPLAY'=>_make('','DISPLAY'));
		$THIS_RET['ID'] = 'GUARDIANS';
		$custom_fields_RET[-1][] = array('CATEGORY'=>'<B>'._("Contact Information").'</B>','ID'=>'GUARDIANS','TITLE'=>''._('Guardians').'','DISPLAY'=>_make('','DISPLAY'));
		$THIS_RET['ID'] = 'ALL_CONTACTS';
		$custom_fields_RET[-1][] = array('CATEGORY'=>'<B>'._("Contact Information").'</B>','ID'=>'ALL_CONTACTS','TITLE'=>''._('All Contacts').'','DISPLAY'=>_make('','DISPLAY'));

		$custom_fields_RET[0][1] = array('CATEGORY'=>'<B>'._("Addresses").'</B>','ID'=>'ADDRESS','TITLE'=>''._('None').'','DISPLAY'=>_makeAddress(''));
		$custom_fields_RET[0][] = array('CATEGORY'=>'<B>'._("Addresses").'</B>','ID'=>'ADDRESS','TITLE'=>'<IMG SRC=assets/house_button.gif>'._( 'Residence').'','DISPLAY'=>_makeAddress('RESIDENCE'));
		$custom_fields_RET[0][] = array('CATEGORY'=>'<B>'._("Addresses").'</B>','ID'=>'ADDRESS','TITLE'=>'<IMG SRC=assets/mailbox_button.gif>'._('Mailing').'','DISPLAY'=>_makeAddress('MAILING'));
		$custom_fields_RET[0][] = array('CATEGORY'=>'<B>'._("Addresses").'</B>','ID'=>'ADDRESS','TITLE'=>'<IMG SRC=assets/bus_button.gif>'._('Bus Pickup').'','DISPLAY'=>_makeAddress('BUS_PICKUP'));
		$custom_fields_RET[0][] = array('CATEGORY'=>'<B>'._('Addresses').'</B>','ID'=>'ADDRESS','TITLE'=>'<IMG SRC=assets/bus_button.gif>'._('Bus Dropoff').'','DISPLAY'=>_makeAddress('BUS_DROPOFF'));

		if(User('PROFILE')=='admin' || User('PROFILE')=='teacher')
			$columns = array('CATEGORY'=>'','TITLE'=>''._('Field').'','SEARCH'=>''._('Search').'','DISPLAY'=>''._('Expanded View').'');
		else
			$columns = array('CATEGORY'=>'','TITLE'=>''._('Field').'','DISPLAY'=>''._('Expanded View').'');
		ListOutputMod($custom_fields_RET,$columns,'','',array(),array(array('CATEGORY')));
	}

	//echo '</fieldset>';
	PopTable('footer');
	if($_REQUEST['tab']=='display_options')
	echo "<CENTER><INPUT type=submit class=\"btn_medium\" value="._('Save')." ></CENTER>";
	else
	echo "<CENTER><INPUT type=submit class=\"btn_medium\" value="._('Save')." onclick='formload_ajax(\"perf_form\");return pass_check();'></CENTER>";
	echo '</FORM>';
}


function _make($value,$name)
{	global $THIS_RET,$categories_RET,$current_RET;

	switch($name)
	{
		case 'SEARCH':
			if($current_RET['StudentFieldsSearch'][$THIS_RET['ID']])
				$checked = ' checked';
			return '<INPUT type=checkbox name=values[StudentFieldsSearch]['.$THIS_RET['ID'].'] value=Y'.$checked.'>';
		break;

		case 'DISPLAY':
			if($current_RET['StudentFieldsView'][$THIS_RET['ID']])
				$checked = ' checked';
			return '<INPUT type=checkbox name=values[StudentFieldsView]['.$THIS_RET['ID'].'] value=Y'.$checked.'>';
		break;
	}
}

function _makeAddress($value)
{	global $current_RET;

	if($current_RET['StudentFieldsView']['ADDRESS'][1]['VALUE']==$value || (!$current_RET['StudentFieldsView']['ADDRESS'][1]['VALUE'] && $value==''))
		$checked = ' CHECKED';
	return '<INPUT type=radio name=values[StudentFieldsView][ADDRESS] value="'.$value.'"'.$checked.'>';
}
?>