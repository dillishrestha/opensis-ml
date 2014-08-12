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
include('../../Redirect_modules.php');
include '_makeLetterGrade.fnc.php';

$course_period_id = UserCoursePeriod();
$course_id = DBGet(DBQuery('SELECT cp.COURSE_ID,c.TITLE FROM course_periods cp,courses c WHERE c.COURSE_ID=cp.COURSE_ID AND cp.COURSE_PERIOD_ID=\''.$course_period_id.'\''));
$course_title = $course_id[1]['TITLE'];
$course_id = $course_id[1]['COURSE_ID'];
##########################################################################
//if($_REQUEST['staff_id'] || (UserStaffID() && $_REQUEST['modfunc']!='save'))
//{   if($_REQUEST['staf_id']=='')
//        $_REQUEST['staff_id']=  UserStaffID();
//    $RET = DBGet(DBQuery("SELECT FIRST_NAME,LAST_NAME FROM STAFF WHERE STAFF_ID='".$_REQUEST['staff_id']."'"));
//    DrawHeaderHome( 'Selected User: '.$RET[1]['FIRST_NAME'].'&nbsp;'.$RET[1]['LAST_NAME'].' (<A HREF=Side.php?staff_id=new&modcat='.clean_param($_REQUEST['modcat'],PARAM_NOTAGS).'><font color=red>Remove</font></A>) | <A HREF=Modules.php?modname='.clean_param($_REQUEST['modname'],PARAM_NOTAGS).'&search_modfunc=list&next_modname=Users/User.php&ajax=true&bottom_back=true&return_session=true target=body>Back to User List</A>');
//}
#####################################################################################

if($_REQUEST['modfunc']=='save')
{
	$config_RET = DBGet(DBQuery('SELECT TITLE,VALUE FROM program_user_config WHERE USER_ID=\''.User('STAFF_ID').'\' AND PROGRAM=\'Gradebook\''),array(),array('TITLE'));
	if(count($config_RET))
		foreach($config_RET as $title=>$value)
			$programconfig[User('STAFF_ID')][$title] = $value[1]['VALUE'];
	else
		$programconfig[User('STAFF_ID')] = true;

	if(count($_REQUEST['st_arr']))
	{
	$st_list = '\''.implode('\',\'',$_REQUEST['st_arr']).'\'';
        $extra['SELECT'] =',ssm.START_DATE';
	$extra['WHERE'] = ' AND s.STUDENT_ID IN ('.$st_list.')';
	Widgets('mailing_labels');
   $extra['moreland_cust'] = 'assignment_grade';
	$RET = GetStuList($extra);
    
	if(count($RET))
	{
		$columns = array('TITLE'=>_('Assignment'));
		if($_REQUEST['assigned_date']=='Y')
			$columns += array('ASSIGNED_DATE'=>_('Assigned Date'));
		if($_REQUEST['due_date']=='Y')
			$columns += array('DUE_DATE'=>_('Due Date'));
		$columns += array('POINTS'=>_('Points'),'LETTER_GRADE'=>_('Grade'),'COMMENT'=>_('Comment'));

		$handle = PDFStart();
		foreach($RET as $student)
		{
			$student_points = $total_points = $percent_weights = array();
			unset($_openSIS['DrawHeader']);
			echo "<meta charset='UTF-8'><table width=100%  style=\" font-family:Arial; font-size:12px;\" >";
			echo "<meta charset='UTF-8'><tr><td width=105>".DrawLogo()."</td><td  style=\"font-size:15px; font-weight:bold; padding-top:20px;\">". GetSchool(UserSchool())."<div style=\"font-size:12px;\">"._('Student Progress Report')."</div></td><td align=right style=\"padding-top:20px;\">". ProperDate(DBDate()) ."<br/>"._('Powered by openSIS')."</td></tr><tr><td colspan=3 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";
			echo '<table border=0 style=\"font-size:12px;\">';
			echo "<meta charset='UTF-8'><tr><td>"._("Student Name").":</td>";
			echo "<td>" .$student['FULL_NAME']. "</td></tr>";
			echo "<meta charset='UTF-8'><tr><td>"._('ID').":</td>";
			echo "<td>". $student['STUDENT_ID'] ." </td></tr>";
			echo "<meta charset='UTF-8'><tr><td>"._('Grade').":</td>";
			echo "<td>". $student['GRADE_ID'] ." </td></tr>";
			
			echo "<meta charset='UTF-8'><tr><td>"._('Marking Period').":</td>";
			echo "<td>". GetMP(UserMP())." </td></tr>";
			echo '</table>';

			#if($_REQUEST['mailing_labels']=='Y')

                         #############################
                         $MP_TYPE_RET=DBGet(DBQuery('SELECT MP_TYPE FROM marking_periods WHERE MARKING_PERIOD_ID='.UserMP().' LIMIT 1'));
                         $MP_TYPE=$MP_TYPE_RET[1]['MP_TYPE'];
                          if($MP_TYPE=='year'){
                        $MP_TYPE='FY';
                        }else if($MP_TYPE=='semester'){$MP_TYPE='SEM';
                        }else if($MP_TYPE=='quarter'){$MP_TYPE='QTR';
                        }else{$MP_TYPE='';
                        }
                        if($MP_TYPE=="QTR")
                                {
                                    $quarter_val=DBGet(DBQuery('SELECT START_DATE, END_DATE FROM school_quarters WHERE MARKING_PERIOD_ID='.UserMP().' '));
                                    $q=$quarter_val[1];
                                    $quarter=DBGet(DBQuery('SELECT MARKING_PERIOD_ID  FROM school_quarters WHERE ((\''.$q['START_DATE'].'\'Between START_DATE And END_DATE ) OR(\''.$q['END_DATE'].'\'Between START_DATE And END_DATE ))  AND SCHOOL_ID=999'));
                                    $EVAL=$quarter[1];

                                }
                            if($MP_TYPE=="SEM")
                                {
                                    $semester_val=DBGet(DBQuery('SELECT START_DATE, END_DATE FROM school_semesters WHERE MARKING_PERIOD_ID='.UserMP().' '));
                                    $q=$semester_val[1];
                                    //  ECHO "SELECT MARKING_PERIOD_ID  FROM SCHOOL_SEMESTERS WHERE START_DATE>='$q[START_DATE]' AND END_DATE<='$q[END_DATE]' AND SCHOOL_ID=999";
                                     $semester=DBGet(DBQuery('SELECT MARKING_PERIOD_ID  FROM school_semesters WHERE ((\''.$q['START_DATE'].'\'Between START_DATE And END_DATE ) OR(\''.$q['END_DATE'].'\'Between START_DATE And END_DATE )) AND SCHOOL_ID=999'));
                                    $EVAL=$semester[1];
                                   
                                  

                                }
                            if($MP_TYPE=="FY")
                                {
                                    $year_val=DBGet(DBQuery('SELECT START_DATE, END_DATE FROM school_years WHERE MARKING_PERIOD_ID='.UserMP().' '));
                                    $q=$year_val[1];
                                    $year=DBGet(DBQuery('SELECT MARKING_PERIOD_ID  FROM school_years WHERE ((\''.$q['START_DATE'].'\'Between START_DATE And END_DATE ) OR(\''.$q['END_DATE'].'\'Between START_DATE And END_DATE )) AND SCHOOL_ID=999 '));
                                    $EVAL=$year[1];

                                }
                            //  $EVAL =   EacedamyGetAllMP($MP_TYPE,$EVAL['MARKING_PERIOD_ID']);
                        ############################## 


			if($_REQUEST['mailing_labels']=='Y')
				echo '<tr><TD colspan=2>'.$student['MAILING_LABEL'].'</TD></TR>';

			
                        
                    $courselist_ret = DBGet(DBQuery('SELECT s.TITLE AS COURSE, s.COURSE_ID, cp.COURSE_PERIOD_ID
                                                        FROM gradebook_grades g, courses s, course_periods cp, gradebook_assignments ga
                                                        WHERE cp.COURSE_PERIOD_ID = ga.COURSE_PERIOD_ID
                                                        AND s.COURSE_ID = cp.COURSE_ID AND ga.assignment_id = g.assignment_id AND ga.marking_period_id =\''.UserMP().'\' and  g.STUDENT_ID=\''.$student[STUDENT_ID].'\' and s.syear=\''.UserSyear().'\' group by cp.COURSE_PERIOD_ID'));
                    foreach($courselist_ret as $courselist=>$course)
                    {
                            unset($student_points);
                            unset($total_points);
                            unset($percent_weights);
                            unset($total_stpoints);
                            unset($total_asgnpoints);
                            $course_title        = $course['COURSE'];
                             $course_id           = $course['COURSE_ID'];
                             $course_period_id    = $course['COURSE_PERIOD_ID'];
                             $course_period_title=DBGet(DBQuery('SELECT TITLE FROM course_periods WHERE COURSE_PERIOD_ID=\''.$course_period_id.'\' '));
                      echo '<table border=0 style=\"font-size:12px;\">';
                      echo "<meta charset='UTF-8'><tr><td>"._('Course').":</td><td>".$course_title ."</td></tr>";
                      echo "<meta charset='UTF-8'><tr><td>"._('Course Period').":</td><td>".$course_period_title[1]['TITLE']."</td></tr>";
                        
                        
			if($programconfig[User('STAFF_ID')]['WEIGHT']=='Y')
				$sql = 'SELECT a.TITLE,a.ASSIGNED_DATE,a.DUE_DATE,      t.ASSIGNMENT_TYPE_ID,     t.FINAL_GRADE_PERCENT,g.POINTS,a.POINTS AS TOTAL_POINTS,g.COMMENT,g.POINTS AS LETTER_GRADE,CASE WHEN (a.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE>=a.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM gradebook_assignment_types t,gradebook_assignments a 
                                        LEFT OUTER JOIN gradebook_grades g ON (a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND g.STUDENT_ID=\''.$student['STUDENT_ID'].'\' AND g.COURSE_PERIOD_ID=\''.$course_period_id.'\') 
                                             WHERE   a.ASSIGNMENT_TYPE_ID=t.ASSIGNMENT_TYPE_ID AND (a.COURSE_PERIOD_ID=\''.$course_period_id.'\' OR a.COURSE_ID=\''.$course_id.'\' ) AND t.COURSE_ID=\''.$course_id.'\' AND a.MARKING_PERIOD_ID=\''.UserMP().'\'';
			else
                                $sql = 'SELECT a.TITLE,a.ASSIGNED_DATE,a.DUE_DATE,\'-1\' AS ASSIGNMENT_TYPE_ID,\'1\' AS FINAL_GRADE_PERCENT,g.POINTS,a.POINTS AS TOTAL_POINTS,g.COMMENT,g.POINTS AS LETTER_GRADE,CASE WHEN (a.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE>=a.DUE_DATE) THEN \'Y\' ELSE NULL END AS DUE FROM    gradebook_assignments a
                                        LEFT OUTER JOIN gradebook_grades g ON (a.ASSIGNMENT_ID=g.ASSIGNMENT_ID AND g.STUDENT_ID=\''.$student['STUDENT_ID'].'\' AND g.COURSE_PERIOD_ID=\''.$course_period_id.'\')
                                             WHERE       (a.COURSE_PERIOD_ID=\''.$course_period_id.'\' OR a.COURSE_ID=\''.$course_id.'\')  AND a.MARKING_PERIOD_ID=\''.UserMP().'\'';
			if($_REQUEST['exclude_notdue']=='Y')
				$sql .= ' AND ((a.ASSIGNED_DATE IS NULL OR CURRENT_DATE>=a.ASSIGNED_DATE) AND (a.DUE_DATE IS NULL OR CURRENT_DATE>=DUE_DATE) OR g.POINTS IS NOT NULL)';
			if($_REQUEST['exclude_ec']=='Y')
				$sql .= ' AND (a.POINTS!=\'0\' OR g.POINTS IS NOT NULL AND g.POINTS!=\'-1\')';
			$sql .=' AND a.DUE_DATE>=\''.$student['START_DATE'].'\' ORDER BY a.ASSIGNMENT_ID';
                       
			$grades_RET = DBGet(DBQuery($sql),array('ASSIGNED_DATE'=>'_removeSpaces','DUE_DATE'=>'_removeSpaces','TITLE'=>'_removeSpaces','POINTS'=>'_makeExtra','LETTER_GRADE'=>'_makeExtra'));

			$sum_points = $sum_percent = 0;
			foreach($percent_weights as $assignment_type_id=>$percent)
			{
				$sum_points += $student_points[$assignment_type_id] * $percent_weights[$assignment_type_id] / $total_points[$assignment_type_id];
				$sum_percent += $percent;
                                $total_stpoints   += $student_points[$assignment_type_id];
                                $total_asgnpoints += $total_points[$assignment_type_id];
			}
			if($sum_percent>0)
				$sum_points /= $sum_percent;
			else
				$sum_points = 0;
                        
                            $link['add']['html'] = array('TITLE'=>'<B>'._('Total').'</B>','LETTER_GRADE'=>'( '.$total_stpoints.' / '.$total_asgnpoints.' ) '.Percent($sum_points).'&nbsp;'._makeLetterGrade($sum_points));
                        
                      
                        
			$link['add']['html']['ASSIGNED_DATE'] = $link['add']['html']['DUE_DATE'] = $link['add']['html']['POINTS'] = $link['add']['html']['COMMENT'] = ' &nbsp; ';
			echo '</table>';
                        
                         if($_REQUEST['list_type']=='total')
			{
			  echo '<table border=0  style=\"font-size:12px;\" >';
                          echo '<tr><td> '>_('Total').':</td><td>'.Percent($sum_points).'&nbsp;'._makeLetterGrade($sum_points).'</td> </tr>';
                          echo '<tr><td></td></tr>';
                          echo '</table>';
                        }
                          else
                              ListOutputPrint($grades_RET,$columns,_('Assignment'),_('Assignments'),$link,array(),array('center'=>false,'add'=>true));
                        unset($percent_weights);
                  }
 
			echo '<div style="page-break-before: always;">&nbsp;</div>';
		}

		PDFStop($handle);
	}
	else
		BackPrompt(_('No Students were found.'));
	}
	else
		BackPrompt(_('You must choose at least one student.'));
}

if(!$_REQUEST['modfunc'])
{
	#DrawHeader('Gradebook - '.ProgramTitle());
        if($_REQUEST['pr']==1)
        {
            $extra['skip_search']='Y';
            $_REQUEST['search_modfunc']='list';
        }
	if($_REQUEST['search_modfunc']=='list') // || UserStudentID())
	{
		echo "<FORM action=for_export.php?modname=$_REQUEST[modname]&modfunc=save&include_inactive=$_REQUEST[include_inactive]&_openSIS_PDF=true method=POST target=_blank>";
		#$extra['header_right'] = '<INPUT type=submit value=\'Create Progress Reports for Selected Students\' class=btn_xxlarge>';

		$extra['extra_header_left'] = '<TABLE>';
		$extra['extra_header_left'] .= '<TR><TD align=right width=120>'._('Assigned Date').'</TD><TD><INPUT type=checkbox value=Y name=assigned_date></TD>';
		$extra['extra_header_left'] .= '<TD align=right>'._('Exclude Ungraded E/C Assignments').'</TD><TD><INPUT type=checkbox value=Y name=exclude_ec checked></TD></TR>';
		$extra['extra_header_left'] .= '<TR><TD align=right width=120>'._('Due Date').'</TD><TD><INPUT type=checkbox value=Y name=due_date checked></TD>';
		$extra['extra_header_left'] .= '<TD align=right>'._('Exclude Ungraded Assignments Not Due').'</TD><TD><INPUT type=checkbox value=Y name=exclude_notdue></TD></TR>';
		//Widgets('mailing_labels');
                $extra['extra_header_left'] .= '<TD align=right>'._('With Assignment Details').' </TD><TD><INPUT type=radio value=detail name=list_type checked=true></TD><TD>'._('Totals')._(' ')._('Only').'<INPUT type=radio value=total name=list_type></TD></TR>';
		$extra['extra_header_left'] .= $extra['search'];
		$extra['search'] = '';
		$extra['extra_header_left'] .= '</TABLE>';

	}

	$extra['link'] = array('FULL_NAME'=>false);
	$extra['SELECT'] = ",s.STUDENT_ID AS CHECKBOX";
	$extra['functions'] = array('CHECKBOX'=>'_makeChooseCheckbox');
	$extra['columns_before'] = array('CHECKBOX'=>'</A><INPUT type=checkbox value=Y name=controller checked onclick="checkAll(this.form,this.form.controller.checked,\'st_arr\');"><A>');
	$extra['options']['search'] = false;
	$extra['new'] = true;
          $extra['moreland_cust'] = 'assignment_grade';

	Search('student_id',$extra,'true');
	if($_REQUEST['search_modfunc']=='list')
	{
            if($_SESSION['count_stu']!=0)
		echo '<BR><CENTER><INPUT type=submit value=\''._('Create Progress Reports for Selected Students').'\'  class=btn_xxlarge></CENTER>';
		echo "</FORM>";
	}
}

function _makeExtra($value,$column)
{	global $THIS_RET,$student_points,$total_points,$percent_weights;

	if($column=='POINTS')
	{
		if($THIS_RET['TOTAL_POINTS']!='0')
			if($value!='-1')
			{
				if(($THIS_RET['DUE'] || $value!='')&& $value!='')
				{
					$student_points[$THIS_RET['ASSIGNMENT_TYPE_ID']] += $value;
					$total_points[$THIS_RET['ASSIGNMENT_TYPE_ID']] += $THIS_RET['TOTAL_POINTS'];
					$percent_weights[$THIS_RET['ASSIGNMENT_TYPE_ID']] = $THIS_RET['FINAL_GRADE_PERCENT'];
				}
				return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD><font size=-1>'.(rtrim(rtrim($value,'0'),'.')+0).'</font></TD><TD><font size=-1>&nbsp;/&nbsp;</font></TD><TD><font size=-1>'.$THIS_RET['TOTAL_POINTS'].'</font></TD></TR></TABLE>';
			}
			else
				return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD><font size=-1>Excluded</font></TD><TD></TD><TD></TD></TR></TABLE>';
		else
		{
			$student_points[$THIS_RET['ASSIGNMENT_TYPE_ID']] += $value;
			return '<TABLE border=0 cellspacing=0 cellpadding=0 class=LO_field><TR><TD><font size=-1>'.(rtrim(rtrim($value,'0'),'.')+0).'</font></TD><TD><font size=-1>&nbsp;/&nbsp;</font></TD><TD><font size=-1>'.$THIS_RET['TOTAL_POINTS'].'</font></TD></TR></TABLE>';
		}
	}
	elseif($column=='LETTER_GRADE')
	{
		if($THIS_RET['TOTAL_POINTS']!='0')
			if($value!='-1')
				if($THIS_RET['DUE'] && $value=='')
                                    return 'Not Graded';
                                else if($THIS_RET['DUE'] || $value!='')
					return Percent($value/$THIS_RET['TOTAL_POINTS'],0).'&nbsp;'. _makeLetterGrade($value/$THIS_RET['TOTAL_POINTS']);
				else
					return 'not due';
			else
				return 'n/a&nbsp;n/a';
		else
			return 'e/c';
	}
}

function _removeSpaces($value,$column)
{
	if($column=='ASSIGNED_DATE' || $column=='DUE_DATE')
		$value = ProperDate($value);

	return str_replace(' ','&nbsp;',str_replace('&','&amp;',$value));
}

function _makeChooseCheckbox($value,$title)
{
	return '<INPUT type=checkbox name=st_arr[] value='.$value.' checked>';
}


?>