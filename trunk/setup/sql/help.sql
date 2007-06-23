-- MySQL dump 10.9
--
-- Host: localhost    Database: nessquik_new
-- ------------------------------------------------------
-- Server version	4.1.20

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `help`
--


/*!40000 ALTER TABLE `help` DISABLE KEYS */;
LOCK TABLES `help` WRITE;
INSERT INTO `help` VALUES (4,2,'If I specify \'all plugins\', and new plugins are released, are those automatically included in my saved scan profile?','Yes. General plugin selection (this includes \'by severity\', \'by family\' and \'all plugins\' will be automatically updated as new plugins are released.');
INSERT INTO `help` VALUES (6,3,'What is ScanMeNow?','ScanMeNow is a web process in which you can easily perform a Nessus scan against your computer using the entire Nessus plugin suite. ScanMeNow can only be run against the computer you are accessing the ScanMeNow web page from.');
INSERT INTO `help` VALUES (7,4,'What are special plugin profiles?','<p>\nSpecial plugin profiles allow you to group plugins together so that to the end user they appear as a single plugin.\n</p>\n<p>\nA typical use case may be that you define a list of critical vulnerabilities that machines must be patched against. When new critical vulnerabilities are declared, you tell your users they must patch, but the users dont know which plugins to add to their scans. Worse yet, if users have created profiles already, then those profiles will not be updated to include the new critical plugins.\n</p>\n<p>\nSpecial plugin profiles solve this. You can string together a list of plugins and then users can schedule scans with that single plugin. All the included plugins will be used. Users can also save this plugin in their scan profile. When you update the special plugin with new plugins, those new plugins are automatically included in every users\' profile who uses your special plugin.\n</p>');
INSERT INTO `help` VALUES (8,4,'Scanners? I need to add scanners?','<p>\nYes and no.\n</p>\n<p>\nNote that this feature is not to be confused with distributed scanning.\n</p>\n<p>\nDuring development, a group onsite identified a need to be able to scan behind their firewall. They maintain their own Nessus server, but really liked the nessquik interface and wanted to use it for their systems too.\n</p>\n<p>\nFrom this, nessquik expanded to include the ability to specify which scanners to run scans on. This settings page allows you to create new scanners so that they are listed in the settings configuration for a scan.\n</p>\n<p>\nNote that after you create a new scanner, you\'re expected to install the nessquik-client on your new Nessus server. If you don\'t, then no scans scheduled for that server will ever occur.\n</p>\n<p>\nOnce again, this feature is a manual way of specifying which scanner to run a scan from.\n</p>');
INSERT INTO `help` VALUES (12,6,'Why do I have to install the nessquik-client on each of my Nessus servers?','<p>\nBelieve me, I\'ve tossed around that idea a lot and from a programming and concurrency point of view, it\'s impossible to use both remote calls to the Nessus server and local calls. There are just too many variables to address in any possible set up. Therefore I simply mandate that you install a nessquik-client on your Nessus server.\n</p>');
INSERT INTO `help` VALUES (13,6,'The plugins table stores the list of all plugins known by the machine updating the table. What if I have a remote scanner that has outdated plugins?','<p>\nThen your scanner will not use those plugins during the scan. There has been discussion of ways to fix this problem by notifying the user if the scanner they have chosen supports the plugins they want to use. This idea has been added to my todo list.\n</p>');
INSERT INTO `help` VALUES (18,7,'Who do I contact if I find a bug, have a suggestion, or otherwise need further help?','Send me, Tim Rupp, an email at tarupp@fnal.gov');
INSERT INTO `help` VALUES (21,8,'Ok, nessquik has apparently gained a lot of \"clicky\" stuff. What exactly is clickable now?','<p>\nYes, it certainly has hasn\'t it.\n</p>\n<p>\nI\'ve tried to keep nessquik\'s new clicky-ness to a minimum, but frankly I like spice and lots of \"wow thats neat\" in my applications. Here\'s where nessquik\'s new clicky-ness now resides (as far as I know).\n</p>\n<span style=\'font-style: italic;\'>create page</span>\n<ul>\n<li>nessquik\'s create page has categories such as \"Scan Choices\" and \"Plugins\" which are now clicky.\n</ul>\n<br>\n<span style=\'font-style: italic;\'>settings page</span>\n<ul>\n<li>profile names are clicky so that you can display individual settings for scan profiles\n<li>categories for this page, such as \"Change Devices\" and \"Change Plugins\" are clicky too.\n<li>the \"schedule on\" feature for individual scans has done away with the calendar and replaced it with clicky-ness. Left click the values in the text box to increase them. <span style=\'font-weight: bold;\'>shift</span> + left click values to decrease them.\n</ul>\n<br>\n<span style=\'font-style: italic;\'>help page</span>\n<ul>\n<li>all the help categories are clicky\n<li>all the help topics are also clicky\n</ul>');
INSERT INTO `help` VALUES (22,9,'How do I search by plugin?','<p>\nType in the search field terms that you are looking for in a plugin. For example, \"web kill\" should return plugins for web attacks that may have the ability to kill the webserver. Results are found and returned as you type. Clear the search box to reset the list of available plugins.\n</p>');
INSERT INTO `help` VALUES (23,9,'Can I specify a mix of Families, Severities, and individual plugins to use?','<p>\nYes, just add them to the list on the right.\n</p>');
INSERT INTO `help` VALUES (24,9,'Can I specify multiple copies of the same plugin?','<p>\nNo. You should be prompted if you try to specify the same plugin twice, or specify a plugin that is already included in a larger set.\n</p>');
INSERT INTO `help` VALUES (25,9,'What sort of input does the list entry take?','<p>\nThe list can contain...\n</p>\n<div style=\'width: 100px; float: left; padding-left: 20px;\'>\n- IP addresses\n</div>\n<div style=\'float: left;\'>172.16.1.1</div>\n<div style=\'width: 100px; float: left; clear: left; padding-left: 20px;\'>\n- IP Ranges\n</div>\n<div style=\'float: left;\'>172.16.1.1-172.16.1.10</div>\n<div style=\'width: 100px; float: left; clear: left; padding-left: 20px;\'>\n- CIDR blocks\n</div>\n<div style=\'float: left;\'>172.16.1.0.24</div>\n<div style=\'width: 100px; float: left; clear: left; padding-left: 20px;\'>\n- Hostnames\n</div>\n<div style=\'float: left;\'>localhost</div>\n<div style=\'width: 100px; float: left; clear: left; padding-left: 20px;\'>\n- VHosts\n</div>\n<div style=\'float: left;\'>[localhost]</div>\n<div style=\'clear: left; padding-top: 10px;\'>\nMake sure you separate items with a comma\n</div>');
INSERT INTO `help` VALUES (27,9,'Why is my \"schedule scan\" button grayed out?','<p>\nWhen there are no known scanners for any of the divisions you\'re a part of, nessquik will disable the scan scheduling and scan updating buttons (scan updating is on the settings page).\n</p>\n<p>\nI\'m doing this because it makes no sense to schedule a scan if the scan can\'t be run.\n</p>');
INSERT INTO `help` VALUES (28,10,'When will I receive my scan results?','<p>\nThe scan runner is fired every minute and is written to fork no more than 10 scans at a time(at the moment) for performance reasons. Results are emailed as the individual scans are finished.\n</p>');
INSERT INTO `help` VALUES (29,10,'Which email will my results be sent to?','Whichever email address is assigned to the _RECIPIENT constant in your config file');
INSERT INTO `help` VALUES (30,10,'What is the format of the results that are emailed to me?','<p>\nText or HTML. This can be specified in both the general and specific scan settings.\n</p>');
INSERT INTO `help` VALUES (31,10,'I changed my general settings but the changes do not affect scans that have been scheduled. Why?','Once a scan has been scheduled, you can only change it\'s settings using the Per-scan Settings options on the Settings page.');
INSERT INTO `help` VALUES (32,10,'Why do I receive empty reports? The report says that one machine was up, but there\'s nothing in the report!','<p>\nMy guess is that you\'ve got one heck of a firewall configured. What is likely happening is that you have a firewall configured and it is blocking every request being sent by Nessus. Nessus in our configuration by default will scan ports 1-15,000. Nothing is showing up because none of those ports are open.\n</p>\n<p>\nSo there are two possible solutions here.\n</p>\n<ol>\n<li>You can just not worry about the scan being empty. It\'s likely a good thing actually because your system is locked down well through the use of the firewall. It\'s not talking back to the good majority of requests that it receives\n<li>You can change the default ports that are scanned. Try bumping the port range up to 65,535 and see what happens. In all likelihood though, there\'s a firewall or ACL somewhere between you and the Nessus server that is blocking parts of the scan.\n</ol>');
INSERT INTO `help` VALUES (33,10,'Why is my scan history shown as empty?','<p>\nThere are three possible reasons\n</p>\n<ol>\n<li>You just created the scan and it hasnt finished running yet, therefore there are no results.\n<li>You have run many scans using that profile but you have decided to delete the scan results since that time.\n<li>You chose not to save the scan results when you made the profile\n</ol>\n<p>\nIn any of the above cases, the only way you\'re going to get another scan history going is to make sure you are having your scans saved, and make sure you are not deleting the results! Once you\'ve done that, you\'ll see your scan history begin to populate again\n</p>');
INSERT INTO `help` VALUES (34,11,'Can you explain how scheduling works?','<p>\nScheduling allows you to configure any number of scans to run at a regular interval once the scan has been saved. This feature more or less allows the admin to create the scan and forget about it. The system then takes care of remembering when to run the scan and send you the results. There are three forms of scheduling. Only one may be applied to an individual scan.\n</p>\n<ul>\n<li>Daily\n<li>Weekly\n<li>Monthly\n</ul>\n<p>\nDaily scheduling involves rescheduling the specified saved scan every day. You can specify a specific hour of the day that you want the scan scheduled, and nessquik will run the scan at that time (give or take 5 minutes).\n</p>\n<p>\nWeekly scheduling involves rescheduling the specified saved scan on a weekly basis. You can specify not only the number of weeks in between the scans and the near-exact time you want to schedule the can, but also the day of the week to run the scan.\n</p>\n<p>\nMonthly scheduling is equally powerful. You can specify the number of months in between scans and the exact time to run the scan on a particular day. nessquik allows you to schedule based on absolute and relative days of the month. Absolute, meaning the 1-31st days of the month, and relative meaning the 4th Monday or 2nd Tuesday of the month.\n</p>');
INSERT INTO `help` VALUES (35,11,'Where\'s the calendar!','<p>\nIt\'s gone! Seriously!\n</p>\nIt was a kludgy solution that I had added because of time constraints. What I needed was actually much more simple and is expressed in the new \"[shift] left click\" functionality.\n<ul>\n<li>left click increases the value\n<li>shift + left click decreases the value\n</ul>');
INSERT INTO `help` VALUES (36,11,'What macros can be put in the email subject line?','<p>\nThe email subject can contain the following macros.\n</p>\n<table style=\'width: 100%;\'>\n<tr>\n<td style=\'font-weight: bold;\'>macro</td>\n<td style=\'font-weight: bold\'>what it puts in the subject</td>\n</tr>\n<tr>\n<td>%m</td>\n<td>Machine being scanned</td>\n</tr>\n<tr>\n<td>%D</td>\n<td>Date the scan started</td>\n</tr>\n<tr>\n<td>%d</td>\n<td>Date the scan finished</td>\n</tr>\n<tr>\n<td>%T</td>\n<td>Time the scan started</td>\n</tr>\n<tr>\n<td>%t</td>\n<td>Time the scan finished</td>\n</tr>\n</table>');
INSERT INTO `help` VALUES (37,11,'Why are my save buttons button grayed out?','<p>\nSee the help topic <span style=\'font-weight: bold\'>Why is my \"schedule scan\" button grayed out?</span> in the <span style=\'font-weight: bold;\'>create</span> category\n</p>');
INSERT INTO `help` VALUES (38,12,'If I specify \'all plugins\', and new plugins are released, are those automatically included in my saved scan profile?','<p>\nYes. General plugin selection (this includes \'by severity\', \'by family\' and \'all plugins\' will be automatically updated as new plugins are released.\n</p>');
INSERT INTO `help` VALUES (39,12,'When I\'m comparing scans, why dont all of my scan profiles show up in the list?','<p>\nYou\'re seeing this because the scan profiles that are \"missing\" do not actually have any saved scan results associated with them. I\'ve decided to filter out those empty profiles because otherwise that information is just clutter for the people using the compare feature.\n</p>');
INSERT INTO `help` VALUES (40,12,'When I\'m viewing my scan history, why dont all of my scan profiles show up in the list?','You\'re seeing this because the scan profiles that are \"missing\" do not actually have any saved scan results associated with them. I\'ve decided to filter out those empty profiles because otherwise that information is just clutter for the people using the view history feature.');
INSERT INTO `help` VALUES (41,12,'Why can\'t I compare scan results from different profiles?','<p>\nBecause I just decided it didn\'t make any sense. Scan profiles as a general rule, should contain a relatively well known list of devices. If you\'re so concerned about seeing the differences between two completely different scans, then please re-think your logic or submit a patch to me that provides the functionality.\n</p>');
INSERT INTO `help` VALUES (42,12,'Hey! I can\'t reschedule a scan that is \'finished\' or \'not ready\'! The button disappeared!','<p>\nYou\'ll see this problem if your administrator removed a scanner from nessquik, and there was a hiccup in the database tables.\n</p>\n<p>\nWhen a scanner is removed from nessquik, nessquik is written to nullify the scanner IDs of all the scan profiles that used the, now deleted, scanner. The reason your button has disappeared is because I didn\'t want people to try to re-schedule a scan if the associated scanner has been deleted. Your re-scheduled scan would have sat forever in the pending queue. By removing your button, I\'m forcing you to go back to your scan settings and choose a new scanner.\n</p>\n<p>\nSo to fix the problem you\'re seeing, you need to go update the settings for the scan profile that is broken, and choose a new scanner to use.\n</p>');
INSERT INTO `help` VALUES (43,13,'What is ScanMeNow?','ScanMeNow is a web process in which you can easily perform a Nessus scan against your computer using the entire Nessus plugin suite. ScanMeNow can only be run against the computer you are accessing the ScanMeNow web page from.');
INSERT INTO `help` VALUES (45,13,'How long will the scan take?','A ScanMeNow with all Nessus plugins can take up to 45 minutes (or longer) to complete.');
INSERT INTO `help` VALUES (46,13,'Can ScanMeNow scan through my firewall?','If your firewall blocks all inbound traffic, ScanMeNow will not be able to detect vulnerabilities present on your system. The good news is that others on the Internet probably will not be able to access your computer either. Because personal firewalls can accidentally be disabled, it is recommended to temporarily permit traffic from the ScanMeNow server, perform the ScanMeNow, then remove the firewall filters you previously added.');
INSERT INTO `help` VALUES (47,13,'What happens if I press the Stop button or refresh the page?','<p>\nIf you press the STOP button on your browser or refresh the webpage, your current scan will continue to run in the background but the results will not be displayed. If you refresh the web page, ScanMeNow will start a new scan against your system while the previous scan continues to run. It is suggested to wait until the ScanMeNow results are displayed before you navigate form the ScanMeNow web page.\n</p>');
INSERT INTO `help` VALUES (48,13,'Can I abort the scan?','<p>\nNo. Once the scan starts, it will run until completion. If you close your web browser, the scan will continue to run against your machine, however the results will be lost.\n</p>');
INSERT INTO `help` VALUES (49,13,'Are the ScanMeNow results saved?','The results of your ScanMeNow are not saved. However, the action of a scan being performed against your machine, along with the date and time, are recorded to syslog.');
INSERT INTO `help` VALUES (50,13,'Can I input additional machines to scan?','No. ScanMeNow is intended to scan only the computer you are accessing the ScanMeNow web page from. To perform scans against multiple machines, you will need to use the standalone Nessus client.');
INSERT INTO `help` VALUES (51,13,'Can I scan a web server?','<p>\nYes. However, ScanMeNow will only detect the default web instance on the IP address being scanned. If you host multiple web servers (or VHOSTS), you should use the standalone Nessus client to ensure you detect all of the available web server installations.\n</p>');
INSERT INTO `help` VALUES (52,13,'Can I scan multiple web servers on a single machine?','No. If you host multiple web servers (or VHOSTS), you should use the standalone Nessus client, or nessquik, to ensure you detect all of the available web server installations.');
INSERT INTO `help` VALUES (54,13,'What browsers are supported?','<p>\nScanMeNow has been tested with Internet Explorer, Firefox, Safari, Mozilla and elinks.\n</p>');
INSERT INTO `help` VALUES (56,13,'I have a system that does not have a web browser (network device, web camera, turnkey system, etc). Can I still use ScanMeNow?','If your system does not have a web browser available, but has the ability to Telnet FROM the system, you can execute the following to access ScanMeNow over a raw Telnet connection (note that the output will be in HTML so you will need to preserve the output into a text file for offline viewing of the results)\n\n\nTo perform a Full scan:\n\n\ntelnet servername 80\nGET /scan-me-now/scanmenow.php?SCANTYPE=F HTTP/1.0\n\'press enter\'\n\'press enter\'\n\n\nIn addition, if you have wget on your system, you can issue the command below which will save the output in a file named scan.html\n\n\nwget Full scan:\n\n\nwget -O scan.html http://servername/scan-me-now/scanmenow.php?SCANTYPE=F');
INSERT INTO `help` VALUES (57,13,'When I print the results, I get a weird output that looks like many items printed on top of itself. What is this?','<p>\nThis is an artifact of the CSS used to display the progress counter. We do not utilize any client side scripting to ensure portability across different platforms (including Telnet access). This artifact should not affect the printing of the actual test results, but if you experience difficulties, it is recommended to cut-and-paste the results table into another application first.\n</p>');
INSERT INTO `help` VALUES (58,13,'Nothing happens when I click on the links in the report.','<p>\nThe links in the report simply take you to the section within the report which explains the results further. If your report is less than one page long, it will seem that the links do not work.\n</p>');
INSERT INTO `help` VALUES (60,13,'I have waited for a long time and still have no results display. What should I do?','First, you should check the progress bar for your browser (this is usually a bar or spinning disk which displays when a web page is loading). If this progress bar is still active, the scan is still running. If this bar is absent and the test counter is \'stuck\' at a number for a very long time, you can try to refresh the web page to restart the scan or you can contact the scan-me-now administrator with the IP address you are scanning from, along with the date and time and we will check for errors.');
INSERT INTO `help` VALUES (61,13,'Why does the scan only complete a portion of the total plugins available?','<p>\nWhile ScanMeNow has over 10,000 tests available, it will only scan for those vulnerabilities possibly present on your system given the open ports, applications or operating system detected. For example, ScanMeNow might not perform Microsft IIS web server checks against a detected Apache installation. It is very unusual for every ScanMeNow test to be performed against your system.\n</p>');
INSERT INTO `help` VALUES (62,13,'What alerts should I be concerned about?','<p>\n<span style=\'font-weight: bold;\'>Vulnerable</span> type alerts\n</p>\n<p>\nYou should address any \'Vulnerable\' type alerts. These alerts represent the greatest threat to you system that can permit an attacker to gain access to your system.\n</p>\n<p>\n<span style=\'font-weight: bold;\'>Warning</span> type alerts\n</p>\n<p>\nYou should review any \'Warning\' type alerts for your system. Many of the \'Warning\' type alerts simply indicate that an attacker may detect the presence of a service on your system and, in many cases, may  safely be ignored UNLESS you do not intend the detected service to be widely available to the general Internet.\n</p>\n<p>\n<span style=\'font-weight: bold;\'>Informational</span> type alerts\n</p>\n<p>\nThese alerts display additional details about the scan that was performed including the host name detected, IP route to your host and other information.\n</p>');
INSERT INTO `help` VALUES (63,13,'Why does my host report as \'dead\'?','If you have a firewall that blocks ICMP messages (PING), your host will be reported as \'dead\' since it did not respond.');
INSERT INTO `help` VALUES (64,14,'What is PortScanMeNow?','PortScanMeNow is a web process in which you can easily perform a Nmap portscan against your computer to determine a list of ports that your computer is listening on and what service is listening. PortScanMeNow can only be run against the computer you are accessing the PortScanMeNow web page from.');
INSERT INTO `help` VALUES (65,14,'How long will the scan take?','<p>\nA PortScanMeNow scan usually takes only a minute to complete. However, depending on the security that is implemented on the client, the scan could take much longer (for instance if a client side firewall is used).\n</p>');
INSERT INTO `help` VALUES (66,14,'Can PortScanMeNow scan through my firewall','If your firewall blocks all inbound traffic, PortScanMeNow will not be able to detect open ports on your system. The good news is that others on the Internet probably will not be able to access your computer either. Because personal firewalls can accidentally be disabled, it is recommended to temporarily permit traffic from the PortScanMeNow server, perform the PortScanMeNow, then remove the firewall filters you previously added.');
INSERT INTO `help` VALUES (67,14,'What happens if I press the Stop button or refresh the page?','<p>\nIf you press the STOP button on your browser or refresh the web page, your current scan will continue to run in the background but the results will not be displayed. If you refresh the web page, PortScanMeNow will start a new scan against your system while the previous scan continues to run. It is suggested to wait until the PortScanMeNow results are displayed before you navigate form the PortScanMeNow web page.\n</p>');
INSERT INTO `help` VALUES (68,14,'Can I abort the scan?','<p>\nNo. Once the scan starts, it will run until completion. If you close your web browser, the scan will continue to run against your machine, however the results will be lost.\n</p>');
INSERT INTO `help` VALUES (69,14,'Are the PortScanMeNow results saved?','The results of your PortScanMeNow are not saved. However, the action of a scan being performed against your machine, along with the date and time, are recorded to syslog.');
INSERT INTO `help` VALUES (70,14,'Can I input additional machines to scan?','<p>\nNo. PortScanMeNow is intended to scan only the computer you are accessing the PortScanMeNow web page from.\n</p>');
INSERT INTO `help` VALUES (72,14,'What browsers are supported?','<p>\nPortScanMeNow has been tested with Internet Explorer, Firefox, Safari, Mozilla and elinks.\n</p>');
INSERT INTO `help` VALUES (73,14,'I have a system that does not have a web browser (network device, web camera, turnkey system, etc). Can I still use PortScanMeNow?','If your system does not have a web browser available, but has the ability to Telnet FROM the system, you can execute the following to access PortScanMeNow over a raw Telnet connection (note that the output will be in HTML so you will need to preserve the output into a text file for offline viewing of the results)\n\nTo Perform a Port Scan:\ntelnet servername 80\nGET /portscan-me-now/portscanmenow.php HTTP/1.0\n\'press enter\'\n\'press enter\'\n\nIn addition, if you have wget on your system, you can issue one of the command below which will save the output in a file named scan.html\n\nwget Port Scan:\nwget -O scan.html http://servername/portscan-me-now/portscanmenow.php');
INSERT INTO `help` VALUES (74,14,'When I print the results, I get a weird output that looks like many items printed on top of itself. What is this?','<p>\nThis is an artifact of the CSS used to display the progress counter. We do not utilize any client side scripting to ensure portability across different platforms (including Telnet access). This artifact should not affect the printing of the actual test results, but if you experience difficulties, it is recommended to cut-and-paste the results table into another application first.\n</p>');
INSERT INTO `help` VALUES (75,14,'How do I use the port address range with wget?','<p>\nSimple. You can do one of two things\n<ul>\n<li>Surf to the portscanmenow page\n<li>Type in a port range\n<li>Right click and copy the link to the left for the type of scan you want to run. The port range is automatically added\n</ul>\nor\n<ul>\n<li>Append the words &PORT=1-65535 to the end of the portscanmenow link. Obviously change the port range to be what you want\n</ul>\n</p>');
INSERT INTO `help` VALUES (77,14,'I have waited for a long time and still have no results display. What should I do?','First, you should check the progress bar for your browser (this is usually a bar or spinning disk which displays when a web page is loading). If this progress bar is still active, the scan is still running. If this bar is absent and the test counter is \'stuck\' at a number for a very long time, you can try to refresh the web page to restart the scan or you can contact the portscan-me-now administrator with the IP address you are scanning from along with the date and time and we will check for errors.');
UNLOCK TABLES;
/*!40000 ALTER TABLE `help` ENABLE KEYS */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
