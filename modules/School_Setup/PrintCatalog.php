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

#######################################################################################################################
include('../../Redirect_modules.php');
	if(clean_param($_REQUEST['modfunc'],PARAM_ALPHAMOD)=='print' && $_REQUEST['report'])
	{
		echo '<style type="text/css">*{font-family:arial; font-size:12px;}</style>';
	
		if(clean_param($_REQUEST['marking_period_id'],PARAM_ALPHANUM))
			$where = ' AND MARKING_PERIOD_ID='.$_REQUEST['marking_period_id'];
               $sql = 'select distinct
				(select title from course_subjects where subject_id=(select subject_id from courses where course_id=course_periods.course_id)) as subject,
				(select title from courses where course_id=course_periods.course_id) as COURSE_TITLE,course_id
				from course_periods where school_id=\''.UserSchool().'\' and syear=\''.UserSyear().'\' '.$where.' order by subject,COURSE_TITLE';

		/*		 $sql = "select
				(select title from course_subjects where subject_id=(select subject_id from courses where 						course_id=course_periods.course_id)) as subject,
				(select title from courses where course_id=course_periods.course_id) as COURSE_TITLE,
				short_name, 
				(select title from school_periods where period_id=course_periods.period_id) as period, 
				marking_period_id, 
				(select CONCAT(LAST_NAME,' ',FIRST_NAME,' ',MIDDLE_NAME,' ') from staff where staff_id=course_periods.teacher_id) as teacher, 
				room as location,days,course_period_id,course_id
				from course_periods where school_id='".UserSchool()."' and syear='".UserSyear()."' ".$where." order by subject,COURSE_TITLE";
		*/

         $ret = DBGet(DBQuery($sql));

		if(count($ret))
		{
			
			foreach($ret as $s_id)
			{
				echo "<table width=100%  style=\" font-family:Arial; font-size:12px;\" >";
				$mark_name_rp = DBGet(DBQuery('SELECT TITLE,SHORT_NAME,\'2\'  FROM school_quarters WHERE MARKING_PERIOD_ID=\''.$_REQUEST['marking_period_id'].'\' AND SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' UNION SELECT TITLE,SHORT_NAME,\'1\'  FROM school_semesters WHERE MARKING_PERIOD_ID=\''.$_REQUEST['marking_period_id'].'\' AND SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' UNION SELECT TITLE,SHORT_NAME,\'0\'  FROM school_years WHERE MARKING_PERIOD_ID=\''.$_REQUEST['marking_period_id'].'\' AND SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' ORDER BY 3'));
		$mark_name_rpt = $mark_name_rp[1]['TITLE'];
		if($mark_name_rpt!='')
		{
				echo "<meta charset='UTF-8'><tr><td width=105>".DrawLogo()."</td><td  style=\"font-size:15px; font-weight:bold; padding-top:20px;\">". GetSchool(UserSchool())."<div style=\"font-size:12px;\">"._('Course')." "._('catalog by Term').": ".$mark_name_rpt."</div></td><td align=right style=\"padding-top:20px;\">". ProperDate(DBDate()) ."<br />"._('Powered by openSIS')."</td></tr><tr><td colspan=3 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";
		 }
		 else
		 {
			 echo "<meta charset='UTF-8'><tr><td width=105>".DrawLogo()."</td><td  style=\"font-size:15px; font-weight:bold; padding-top:20px;\">". GetSchool(UserSchool())."<div style=\"font-size:12px;\">"._('Course').""._(' catalog by Term').": "._('All')."</div></td><td align=right style=\"padding-top:20px;\">". ProperDate(DBDate()) ."<br />"._('Powered by openSIS')."</td></tr><tr><td colspan=3 style=\"border-top:1px solid #333;\">&nbsp;</td></tr></table>";
		 }

			echo '<div align="center">';
			echo '<table border="0" width="97%" align="center"><tr><td><font face=verdana size=-1><b>'.$s_id['SUBJECT'].'</b></font></td></tr>';
			echo '<tr><td align="right"><table border="0" width="97%"><tr><td><font face=verdana size=-1><b>'.$s_id['COURSE_TITLE'].'</b></font></td></tr>';
			
			
			if(!$_REQUEST['marking_period_id'])
			{
			 	#$sql_periods = "SELECT cp.SHORT_NAME,(SELECT TITLE FROM school_periods WHERE period_id=cp.period_id) AS PERIOD,cp.ROOM,cp.DAYS,(SELECT CONCAT(LAST_NAME,' ',FIRST_NAME,' ',MIDDLE_NAME,' ') from staff where staff_id=cp.TEACHER_ID) as TEACHER from course_periods cp where cp.course_id=".$s_id['COURSE_ID']." and cp.syear='".UserSyear()."' and cp.school_id='".UserSchool()."'";
			 	$sql_periods = 'SELECT cp.SHORT_NAME,(SELECT TITLE FROM school_periods WHERE period_id=cp.period_id) AS PERIOD,cp.ROOM,cp.DAYS,(SELECT CONCAT(LAST_NAME,\' \',FIRST_NAME,\' \') from staff where staff_id=cp.TEACHER_ID) as TEACHER from course_periods cp where cp.course_id='.$s_id['COURSE_ID'].' and cp.syear=\''.UserSyear().'\' and cp.school_id=\''.UserSchool().'\'';
				
			 }
			 else
			 {
			 	#$sql_periods = "SELECT distinct cp.SHORT_NAME,(select CONCAT(START_TIME,' - ',END_TIME,' ') from school_periods where period_id=cp.period_id) as PERIOD,cp.ROOM,cp.DAYS,(select CONCAT(LAST_NAME,' ',FIRST_NAME,' ',MIDDLE_NAME,' ') from staff where staff_id=cp.TEACHER_ID) as TEACHER from course_periods cp where cp.course_id=".$s_id['COURSE_ID']." and cp.syear='".UserSyear()."' and cp.school_id='".UserSchool()."'";
				 $sql_periods = 'SELECT distinct cp.SHORT_NAME,(select CONCAT(START_TIME,\' - \',END_TIME,\' \') from school_periods where period_id=cp.period_id) as PERIOD,cp.ROOM,cp.DAYS,(select CONCAT(LAST_NAME,\' \',FIRST_NAME,\' \') from staff where staff_id=cp.TEACHER_ID) as TEACHER from course_periods cp where cp.course_id='.$s_id['COURSE_ID'].' and cp.syear=\''.UserSyear().'\' and cp.school_id=\''.UserSchool().'\' and cp.marking_period_id=\''.$_REQUEST['marking_period_id'].'\'';

		}	
			
			
			
			$period_list = DBGet(DBQuery($sql_periods));
			
##############################################List Output Generation##################################################
			
			$columns = array('SHORT_NAME'=>''._('Course').'','PERIOD'=>''._('Time').'','DAYS'=>''._('Days').'','ROOM'=>''._('Location').'','TEACHER'=>''._('Teacher').'');
			
			echo '<tr><td colspan="2" valign="top" align="right">';
			PrintCatalog($period_list,$columns,''._('Course').'',''._('Courses').'','','',array('search'=>false));
			echo '</td></tr></table></td></tr></table></td></tr>';
			
			######################################################################################################################
			echo '</table></div>';
	
			echo "<div style=\"page-break-before: always;\"></div>";
			}
		}
		else
		echo '<table width=100%><tr><td align=center><font color=red face=verdana size=2><strong>'._('No Courses are found in this term').'</strong></font></td></tr></table>';
		
		
	}
	else
	{
	PopTable('header',''._('Print Catalog by Term').'','width=700');
	echo '<table width=100%><tr><td>';
	echo "<FORM id='search' name='search' method=POST action=Modules.php?modname=$_REQUEST[modname]>";
	$mp_RET = DBGet(DBQuery('SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,\'2\'  FROM school_quarters WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' UNION SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,\'1\'  FROM school_semesters WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' UNION SELECT MARKING_PERIOD_ID,TITLE,SHORT_NAME,\'0\'  FROM school_years WHERE SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' ORDER BY 3'));
				unset($options);
		if(count($mp_RET))
			{
				foreach($mp_RET as $key=>$value)
				{
					if($value['MARKING_PERIOD_ID']==$_REQUEST['marking_period_id'])
						$mp_RET[$key]['row_color'] = Preferences('HIGHLIGHT');
				}
				
		$columns = array('TITLE'=>''._('Marking Periods').'');
		$link = array();
		$link['TITLE']['link'] = "Modules.php?modname=$_REQUEST[modname]";
		$link['TITLE']['variables'] = array('marking_period_id'=>'MARKING_PERIOD_ID', 'mp_name' => 'SHORT_NAME');
		$link['TITLE']['link'] .= "&modfunc=$_REQUEST[modfunc]";

		echo CreateSelect($mp_RET, 'marking_period_id', ''._('All').'', ''._('Select Term').': ', 'Modules.php?modname='.$_REQUEST['modname'].'&marking_period_id=');
			}	
	echo '</td></tr></table>';
	if(clean_param($_REQUEST['marking_period_id'],PARAM_ALPHANUM))
	{
	$mark_name = DBGet(DBQuery('SELECT TITLE,SHORT_NAME,\'2\'  FROM school_quarters WHERE MARKING_PERIOD_ID=\''.$_REQUEST['marking_period_id'].'\' AND SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' UNION SELECT TITLE,SHORT_NAME,\'1\'  FROM school_semesters WHERE MARKING_PERIOD_ID=\''.$_REQUEST['marking_period_id'].'\' AND SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' UNION SELECT TITLE,SHORT_NAME,\'0\'  FROM school_years WHERE MARKING_PERIOD_ID=\''.$_REQUEST['marking_period_id'].'\' AND SCHOOL_ID=\''.UserSchool().'\' AND SYEAR=\''.UserSyear().'\' ORDER BY 3'));
	$mark_name = $mark_name[1]['SHORT_NAME'];
	DrawHeader('<div align="center">'._('Report generated for').''._( $mark_name ).''._('Term').'</div>');
	}
	else
	DrawHeader('<div align="center">'._('Report generated for all Terms').'</div>');	
	echo '</form>';
	echo "<FORM name=exp id=exp action=for_export.php?modname=$_REQUEST[modname]&modfunc=print&marking_period_id=".$_REQUEST['marking_period_id']."&_openSIS_PDF=true&report=true method=POST target=_blank>";
	echo '<table width=100%><tr><td align="center"><INPUT type=submit class=btn_medium value=\''._('Print').'\'></td></tr></table>';
	echo '</form>';
	PopTable('footer');
}






##########Functions###################
	function CreateSelect($val, $name, $opt, $cap, $link)
	{
		$html .= "<table width=100% border=0><tr><td width=40% align=center>";
		$html .= $cap;
		$html .= "<select name=".$name." id=".$name." onChange=\"window.location='".$link."' + this.options[this.selectedIndex].value;\">";
		$html .= "<option value=''>".$opt."</option>";
		
				foreach($val as $key=>$value)
				{
					if($value[strtoupper($name)]==$_REQUEST[$name])
						$html .= "<option selected value=".$value[strtoupper($name)].">".$value['TITLE']."</option>";
					else
						$html .= "<option value=".$value[strtoupper($name)].">".$value['TITLE']."</option>";	
				}
		
		
		$html .= "</select></td></tr></table>";		
		return $html;
	}

?>
