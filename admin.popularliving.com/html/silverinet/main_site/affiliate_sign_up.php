<?php



include_once "includes/config.php";
include_once "includes/library.php";



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Silver iNET</title>
<link rel="stylesheet" type="text/css" href="style/style.css" />
<script src="js/javascript.js" type="text/javascript"></script>
<script src="javascript/form_validation_functions_v3.js" type="text/javascript"></script>
<script src="javascript/site_functions.js" type="text/javascript"></script>
</head>

<body id="affiliate_sign_up">
	<!--begin wrapper-->
    <div id="wrapper">
    	<!--begin search_bar-->
    	<div id="search_bar">
    		<h1><a href="index.php"><img src="images/logo.gif" width="411" height="60" alt="Silver iNET" /></a></h1>	
            <form action="https://partners.cpacoreg.com" method="post">
                <p><input type="hidden" name="next" value="" /></p>
                <ul>
                	<li><label for="username">Username:</label> <input type="text" id="username" name="DL_AUTH_USERNAME" value="" />
                    <a href="javascript:openWin('http://partners.cpacoreg.com/forgot_password.html')">Forgot Password?</a>
                    </li>
                	<li><label for="password">Password:</label> <input type="password" id="password" name="DL_AUTH_PASSWORD" value="" /></li>
                </ul>
                <p><input class="login_button" type="image" src="images/login-off.gif" value="Submit" alt="Submit" /></p>
			</form>
        </div>
        <!--end search_bar-->
        
        <!-- end search_bar-->
		<div id="main_nav">
			<ul id="nav">
				<li id="home"><a href="index.php">home</a></li>
				<li id="about"><a href="about_us.php">about us</a></li>
				<li id="affiliate" class="here"><a href="affiliate_sign_up.php">affiliate sign up</a></li>
				<li id="advertiser"><a href="advertiser_sign_up.php">advertiser sign up</a></li>
                <li id="contact"><a href="contact_us.php">contact us</a></li>
            </ul>
		</div>
		<!-- end main_nav-->
        
        <!-- begin content -->
        <div id="content">
        	<!-- begin content_bot -->
        	<div id="content_bot">
            	<!-- begin content_inner -->
            	<div id="content_inner">
                	<h2>Affiliate Sign Up</h2><?php echo getVar('affiliateText');?>
	
	
					<?php
					if ($message!="") {
						?>
						<h3 id="message"><?php echo $message;?></h3>
						<?php
					}
					?>
        
                    <form action="https://partners.cpacoreg.com/affiliate_signup.html?submitted=1" method="post">
						<p><input type="hidden" name="task" value="process" /></p>
						<p><input type="hidden" name="error_td" value="1" /></p>
						<p><input type="hidden" name="numeric_fields" value="traffic" /></p>
						<p><input type="hidden" name="required_fields" value="first_name,last_name,email,password,password2,address,city,select_state,zip,country,phone,fax,website1,category_1,tax_id,site_type,site_description" /></p>
                    <!-- begin twocol -->
                    
                    <h3>Publisher Information</h3>
                    
                    <div class="twocol">
                        <div class="col">
                            <p><label for="first_name"><span>*</span> First Name:</label> <input id="first_name" type="text" name="first_name" value="" /></p>
                            <p><label for="last_name"><span>*</span> Last Name:</label> <input id="last_name" type="text" name="last_name" value="" /></p>
                            <p><label for="email"><span>*</span> Email:</label> <input id="email" type="text" name="email" value="" /></p>
                            <p><label for="password"><span>*</span> Password:</label> <input id="password" type="text" name="password" value="" /></p>
                            <p><label for="password2"><span>*</span> Confirm Password:</label> <input id="password2" type="text" name="password2" value="" /></p>
                         	<p><label for="company"><span>*</span>Company:</label> <input id="company" type="text" name="company" value="" /></p>
                            <p><label for="address"><span>*</span> Address:</label> <input id="address" type="text" name="address" value="" /></p>
                            <p><input id="address2" type="text" name="address2" value="" /></p>
                            
                         </div>
                         <div class="col">
                            <p><label for="city"><span>*</span> City:</label> <input id="city" type="text" name="city" value="" /></p>
                            <p><label for="state"><span>*</span> State:</label>
                            	<select id="state" name="select_state">
                                    <option value="0"></option>
                                    <option value="1">N/A</option>
                                    <option value="2">Alabama</option>
                                    <option value="3">Alaska</option>
                                    <option value="4">Arizona</option>
                                    <option value="5">Arkansas</option>
                                    <option value="6">California</option>
                                    <option value="7">Colorado</option>
                                    <option value="8">Connecticut</option>
                                    <option value="9">Delaware</option>
                                    <option value="10">District of Columbia</option>
                                    <option value="11">Florida</option>
                                    <option value="12">Georgia</option>
                                    <option value="13">Hawaii</option>
                                    <option value="14">Idaho</option>
                                    <option value="15">Illinois</option>
                                    <option value="16">Indiana</option>
                                    <option value="17">Iowa</option>
                                    <option value="18">Kansas</option>
                                    <option value="19">Kentucky</option>
                                    <option value="20">Louisiana</option>
                                    <option value="21">Maine</option>
                                    <option value="22">Maryland</option>
                                    <option value="23">Massachusetts</option>
                                    <option value="24">Michigan</option>
                                    <option value="25">Minnesota</option>
                                    <option value="26">Mississippi</option>
                                    <option value="27">Missouri</option>
                                    <option value="28">Montana</option>
                                    <option value="29">Nebraska</option>
                                    <option value="30">Nevada</option>
                                    <option value="31">New Hampshire</option>
                                    <option value="32">New Jersey</option>
                                    <option value="33">New Mexico</option>
                                    <option value="34">New York</option>
                                    <option value="35">North Carolina</option>
                                    <option value="36">North Dakota</option>
                                    <option value="37">Ohio</option>
                                    <option value="38">Oklahoma</option>
                                    <option value="39">Oregon</option>
                                    <option value="40">Pennsylvania</option>
                                    <option value="41">Rhode Island</option>
                                    <option value="42">South Carolina</option>
                                    <option value="43">South Dakota</option>
                                    <option value="44">Tennessee</option>
                                    <option value="45">Texas</option>
                                    <option value="46">Utah</option>
                                    <option value="47">Vermont</option>
                                    <option value="48">Virginia</option>
                                    <option value="49">Washington</option>
                                    <option value="50">West Virginia</option>
                                    <option value="51">Wisconsin</option>
                                    <option value="52">Wyoming</option>
                                    <option value="53">Alberta</option>
                                    <option value="54">British Columbia</option>
                                    <option value="55">Manitoba</option>
                                    <option value="56">Newfoundland</option>
                                    <option value="57">New Brunswick</option>
                                    <option value="58">Nova Scotia</option>
                                    <option value="59">Northwest Territories</option>
                                    <option value="60">Ontario</option>
                                    <option value="61">Prince Edward Island</option>
                                    <option value="62">Quebec</option>
                                    <option value="63">Saskatchewan</option>
                                    <option value="64">Yukon Territory</option>
                                </select>
                            </p>
                         	<p><label for="other_not_state">Other:</label> <input id="other_not_state" type="text" name="other_not_state" value="" /></p>
                            <p><label for="zip"><span>*</span> Zip/Postcode:</label> <input id="zip" type="text" name="zip" value="" /></p>
                            <p><label for="country"><span>*</span> Country:</label>
                            	<select name="country">
									<option value=''>Select a Country</option>
                                    <option value='USA'>United States</option>
                                    <option value='ALB'>Albania</option>
                                    <option value='DZA'>Algeria</option>
                                    <option value='ASM'>American Samoa</option>
                                    <option value='AND'>Andorra</option>
                                    <option value='AGO'>Angola</option>
                                    <option value='AIA'>Anguilla</option>
                                    <option value='ATA'>Antarctica</option>
                                    <option value='ATG'>Antigua and Barbuda</option>
                                    <option value='ARG'>Argentina</option>
                                    <option value='ARM'>Armenia</option>
                                    <option value='ABW'>Aruba</option>
                                    <option value='AUS'>Australia</option>
                                    <option value='AUT'>Austria</option>
                                    <option value='AZE'>Azerbaijan</option>
                                    <option value='BHS'>Bahamas</option>
                                    <option value='BHR'>Bahrain</option>
                                    <option value='BGD'>Bangladesh</option>
                                    <option value='BRB'>Barbados</option>
                                    <option value='BLR'>Belarus</option>
                                    <option value='BEL'>Belgium</option>
                                    <option value='BLZ'>Belize</option>
                                    <option value='BEN'>Benin</option>
                                    <option value='BMU'>Bermuda</option>
                                    <option value='BTN'>Bhutan</option>
                                    <option value='BOL'>Bolivia</option>
                                    <option value='BIH'>Bosnia And Herzegovina</option>
                                    <option value='BWA'>Botswana</option>
                                    <option value='BVT'>Bouvet Island</option>
                                    <option value='BRA'>Brazil</option>
                                    <option value='IOT'>British Indian Ocean Terr.</option>
                                    <option value='BRN'>Brunei</option>
                                    <option value='BGR'>Bulgaria</option>
                                    <option value='BFA'>Burkina Faso</option>
                                    <option value='BDI'>Burundi</option>
                                    <option value='KHM'>Cambodia</option>
                                    <option value='CMR'>Cameroon</option>
                                    <option value='CAN'>Canada</option>
                                    <option value='CPV'>Cape Verde</option>
                                    <option value='CYM'>Cayman Islands</option>
                                    <option value='CAF'>Central African Republic</option>
                                    <option value='TCD'>Chad</option>
                                    <option value='CHL'>Chile</option>
                                    <option value='CHN'>China</option>
                                    <option value='CXR'>Christmas Island</option>
                                    <option value='CCK'>Cocos(Keeling) Islands</option>
                                    <option value='COL'>Columbia</option>
                                    <option value='COM'>Comoros</option>
                                    <option value='COG'>Congo</option>
                                    <option value='COK'>Cook Islands</option>
                                    <option value='CRI'>Costa Rica</option>
                                    <option value='HRV'>Croatia</option>
                                    <option value='CYP'>Cyprus</option>
                                    <option value='CZE'>Czech Republic</option>
                                    <option value='DNK'>Denmark</option>
                                    <option value='DJI'>Djibouti</option>
                                    <option value='DMA'>Dominica</option>
                                    <option value='DOM'>Dominican Republic</option>
                                    <option value='TMP'>East Timor</option>
                                    <option value='ECU'>Ecuador</option>
                                    <option value='EGY'>Egypt</option>
                                    <option value='SLV'>El Salvador</option>
                                    <option value='GNQ'>Equatorial Guinea</option>
                                    <option value='ERI'>Eritrea</option>
                                    <option value='EST'>Estonia</option>
                                    <option value='ETH'>Ethiopia</option>
                                    <option value='FRO'>Faeroe (Faroe) Islands</option>
                                    <option value='FLK'>Falkland Islands</option>
                                    <option value='FJI'>Fiji</option>
                                    <option value='FIN'>Finland</option>
                                    <option value='FRA'>France</option>
                                    <option value='FXX'>France Metropolitan</option>
                                    <option value='GUF'>French Guyana</option>
                                    <option value='PYF'>French Polynesia</option>
                                    <option value='ATF'>French Southern Territories</option>
                                    <option value='GAB'>Gabon</option>
                                    <option value='GMB'>Gambia</option>
                                    <option value='GEO'>Georgia</option>
                                    <option value='DEU'>Germany</option>
                                    <option value='GHA'>Ghana</option>
                                    <option value='GIB'>Gibraltar</option>
                                    <option value='GRC'>Greece</option>
                                    <option value='GRL'>Greenland</option>
                                    <option value='GRD'>Grenada</option>
                                    <option value='GLP'>Guadeloupe</option>
                                    <option value='GUM'>Guam</option>
                                    <option value='GTM'>Guatemala</option>
                                    <option value='GIN'>Guinea</option>
                                    <option value='GNB'>Guinea-Bissau</option>
                                    <option value='GUY'>Guyana</option>
                                    <option value='HTI'>Haiti</option>
                                    <option value='HMD'>Heard and McDonald Islands</option>
                                    <option value='HND'>Honduras</option>
                                    <option value='HKG'>Hong Kong</option>
                                    <option value='HUN'>Hungary</option>
                                    <option value='ISL'>Iceland</option>
                                    <option value='IND'>India</option>
                                    <option value='IDN'>Indonesia</option>
                                    <option value='IRL'>Ireland</option>
                                    <option value='ISR'>Israel</option>
                                    <option value='ITA'>Italy</option>
                                    <option value='CIV'>Ivory Coast</option>
                                    <option value='JAM'>Jamaica</option>
                                    <option value='JPN'>Japan</option>
                                    <option value='JOR'>Jordan</option>
                                    <option value='KAZ'>Kazakhstan</option>
                                    <option value='KEN'>Kenya</option>
                                    <option value='KIR'>Kiribati</option>
                                    <option value='KOR'>Korea Republic Of</option>
                                    <option value='KWT'>Kuwait</option>
                                    <option value='KGZ'>Kyrgyzstan</option>
                                    <option value='LAO'>Laos Peoples Dem. Rep.</option>
                                    <option value='LVA'>Latvia</option>
                                    <option value='LBN'>Lebanon</option>
                                    <option value='LSO'>Lesotho</option>
                                    <option value='LBR'>Liberia</option>
                                    <option value='LIE'>Liechtenstein</option>
                                    <option value='LTU'>Lithuania</option>
                                    <option value='LUX'>Luxembourg</option>
                                    <option value='MAC'>Macao</option>
                                    <option value='MKD'>Macedonia</option>
                                    <option value='MDG'>Madagascar</option>
                                    <option value='MWI'>Malawi</option>
                                    <option value='MYS'>Malaysia</option>
                                    <option value='MDV'>Maldives</option>
                                    <option value='MLI'>Mali</option>
                                    <option value='MLT'>Malta</option>
                                    <option value='MHL'>Marshall Islands</option>
                                    <option value='MTQ'>Martinique</option>
                                    <option value='MRT'>Mauritania</option>
                                    <option value='MUS'>Mauritius</option>
                                    <option value='MYT'>Mayotte</option>
                                    <option value='MEX'>Mexico</option>
                                    <option value='FSM'>Micronesia</option>
                                    <option value='MDA'>Moldova</option>
                                    <option value='MCO'>Monaco</option>
                                    <option value='MNG'>Mongolia</option>
                                    <option value='MSR'>Montserrat</option>
                                    <option value='MAR'>Morocco</option>
                                    <option value='MOZ'>Mozambique</option>
                                    <option value='MMR'>Myanmar</option>
                                    <option value='NAM'>Namibia</option>
                                    <option value='NRU'>Nauru</option>
                                    <option value='NPL'>Nepal</option>
                                    <option value='NLD'>Netherlands</option>
                                    <option value='ANT'>Netherlands Ant</option>
                                    <option value='NCL'>New Caledonia</option>
                                    <option value='NZL'>New Zealand</option>
                                    <option value='NIC'>Nicaragua</option>
                                    <option value='NER'>Niger</option>
                                    <option value='NGA'>Nigeria</option>
                                    <option value='NIU'>Niue</option>
                                    <option value='NFK'>Norfolk Island</option>
                                    <option value='MNP'>Northern Mariana Island</option>
                                    <option value='NOR'>Norway</option>
                                    <option value='OMN'>Oman</option>
                                    <option value='PAK'>Pakistan</option>
                                    <option value='PLW'>Palau</option>
                                    <option value='PAN'>Panama</option>
                                    <option value='PNG'>Papuan W Guinea</option>
                                    <option value='PRY'>Paraguay</option>
                                    <option value='PER'>Peru</option>
                                    <option value='PHL'>Philippines</option>
                                    <option value='PCN'>Pitcairn Island</option>
                                    <option value='POL'>Poland</option>
                                    <option value='PRT'>Portugal</option>
                                    <option value='PRI'>Puerto Rico</option>
                                    <option value='QAT'>Qatar</option>
                                    <option value='REU'>Reunion</option>
                                    <option value='ROM'>Romania</option>
                                    <option value='RUS'>Russian Federation</option>
                                    <option value='RWA'>Rwanda</option>
                                    <option value='SGS'>S. Georgia and the S. Sandwich Island</option>
                                    <option value='WSM'>Samoa</option>
                                    <option value='SMR'>San Marino</option>
                                    <option value='STP'>Saotome and Principe</option>
                                    <option value='SAU'>Saudi Arabia</option>
                                    <option value='SEN'>Senegal</option>
                                    <option value='SYC'>Seychelles</option>
                                    <option value='SLE'>Sierra Leone</option>
                                    <option value='SGP'>Singapore</option>
                                    <option value='SVK'>Slovakia</option>
                                    <option value='SVN'>Slovenia</option>
                                    <option value='SLB'>Solomon Islands</option>
                                    <option value='SOM'>Somalia</option>
                                    <option value='ZAF'>South Africa</option>
                                    <option value='ESP'>Spain</option>
                                    <option value='LKA'>Sri Lanka</option>
                                    <option value='SPM'>St .Pierre and Miquelon</option>
                                    <option value='SHN'>St. Helena</option>
                                    <option value='LCA'>St. Lucia</option>
                                    <option value='KNA'>St.Kitts-Nevis-Anguilla</option>
                                    <option value='VCT'>St.Vincent and the Grenadines</option>
                                    <option value='SUR'>Suriname</option>
                                    <option value='SJM'>Svalbard and Janmayen Islands</option>
                                    <option value='SWZ'>Swaziland</option>
                                    <option value='SWE'>Sweden</option>
                                    <option value='CHE'>Switzerland</option>
                                    <option value='TWN'>Taiwan</option>
                                    <option value='TJK'>Tajikistan</option>
                                    <option value='TZA'>Tanzania</option>
                                    <option value='THA'>Thailand</option>
                                    <option value='TGO'>Togo</option>
                                    <option value='TKL'>Tokelau</option>
                                    <option value='TON'>Tonga</option>
                                    <option value='TTO'>Trinidad and Tobago</option>
                                    <option value='TUN'>Tunisia</option>
                                    <option value='TUR'>Turkey</option>
                                    <option value='TKM'>Turkmenistan</option>
                                    <option value='TCA'>Turks and Caicos Islands</option>
                                    <option value='TUV'>Tuvalu</option>
                                    <option value='UGA'>Uganda</option>
                                    <option value='UKR'>Ukraine SSR</option>
                                    <option value='ARE'>United Arab Emirates</option>
                                    <option value='GBR'>United Kingdom</option>
                                    <option value='UMI'>United States Minor Outlying Island</option>
                                    <option value='URY'>Uruguay</option>
                                    <option value='UZB'>Uzbekistan</option>
                                    <option value='VUT'>Vanuatu</option>
                                    <option value='VAT'>Vatican City-State</option>
                                    <option value='VEN'>Venezuela</option>
                                    <option value='VNM'>Vietnam</option>
                                    <option value='VGB'>Virgin Islands (British)</option>
                                    <option value='VIR'>Virgin Islands (U.S.)</option>
                                    <option value='WLF'>Wallis And Futuna Islands</option>
                                    <option value='ESH'>Western Sahara</option><option value='YEM'>Yemen</option>
                                    <option value='YUG'>Yugoslavia</option>
                                    <option value='ZAR'>Zaire</option>
                                    <option value='ZMB'>Zambia</option>
                                    <option value='ZWE'>Zimbabwe</option>
                            	</select>
                            </p>
                            <p><label for="phone"><span>*</span> Phone:</label> <input id="phone" type="text" name="phone" value="" /></p>
                            <p><label for="fax"><span>*</span> Fax:</label> <input id="fax" type="text" name="fax" value="" /></p>
                         </div>
                    </div>
                    <!-- end twocol -->
                    
                    <h3>Payment Information</h3>
                    	<!-- begin onecol -->
                        <div class="onecol">
                            <p><label for="tax_id"><span>*</span> SS#/Corp ID#/ABN:</label> <input id="tax_id" type="text" name="tax_id" value="" /></p>
                            <p><label for="payment_name_type">Make Payment To:</label> 
                            	<select id="payment_name_type" name="payment_name_type" class="textField">
                                    <option value="Company Name" >Company Name</option>
                                    <option value="Personal Name" >Personal Name</option>
                                </select>
                            </p>
                            <p><label for="PAYMENT_THRESHOLD">Payment Threshold:</label>
                            	<select id="PAYMENT_THRESHOLD" name="PAYMENT_THRESHOLD" class="textField">
                                    <option value="250">$250</option>
                                    <option value="500">$500</option>
                                    <option value="1000">$1000</option>
                                </select>
                            </p>
                    	</div>
                        <!-- end onecol -->
                        
                        <h3>Site Information</h3>
                        
                        <div class="twocol">
                        	<div class="col">
                            	<p>&nbsp;</p>
                            	<p><label for="website1"><span>*</span> Website 1:</label> <input id="website1" type="text" name="website1" value="" /></p>
                                <p><label for="website2">Website 2:</label> <input id="website2" type="text" name="website2" value="" /></p>
                                <p><label for="website3">Who Referred You to the Network?:</label>
                                	<select id="website3" name="website3" onChange="showOtherOption(this);">
										<?php echo getVar('networkReferrerOptions');?>
									</select>
                                </p>
                                
                                <p><label for="site_type"><span>*</span> Site Type:</label>
                                	<select id="site_type" name="site_type">
                                        <option value="0">-- Select a Site type --</option>
                                        <option value="1">Newsletter</option>
                                        <option value="2">Website</option>
                                        <option value="3">Email</option>
                                        <option value="4">Other</option>
                                    </select>
                                </p>
                                <p><label for="traffic">Site Views/Month:</label> <input id="traffic" type="text" name="traffic" value="" /></p>
                                <p><label for="site_description"><span>*</span> Site Description:</label><br />
                                <textarea id="site_description" name="site_description" rows="5" cols="15"></textarea></p>
                                
                            </div>
                            
                            <div class="col">
                                <p><span>Select the categories your site falls under:</span></p>
                                <p><label for="category_1"><span>*</span> Type of Marketing Promotions:</label>
                                <select id="category_1" name="category_1">
                                    <option value="0">-- Select a Category --</option>
                                    <option value="1">Arts and Crafts</option>
                                    <option value="2">Automotive</option>
                                    <option value="3">Babies/Children/Family</option>
                                    <option value="4">Business to Business/Financial</option>
                                    <option value="5">Careers/Employment/Education</option>
                                    <option value="6">Community</option>
                                    <option value="7">Computers and Technology</option>
                                    <option value="8">Coupons/Refunds</option>
                                    <option value="9">E-Commerce/Shopping/Catalog</option>
                                    <option value="10">Entertainment/Music</option>
                                    <option value="11">Food and Grocery</option>
                                    <option value="12">Free Stuff Site</option>
                                    <option value="13">Games</option>
                                    <option value="14">Health/Medical</option>
                                    <option value="15">Humor</option>
                                    <option value="16">International</option>
                                    <option value="17">ISP/Portal/Search/Directory</option>
                                    <option value="18">Local</option>
                                    <option value="19">Magazines and News</option>
                                    <option value="20">Pets/Animals</option>
                                    <option value="21">Real Estate</option>
                                    <option value="22">Religion</option>
                                    <option value="23">Rewards and Sweepstakes</option>
                                    <option value="24">Self Improvement</option>
                                    <option value="25">Sports</option>
                                    <option value="26">Teen/College</option>
                                    <option value="27">Travel/Tourism</option>
                                    <option value="28">Women</option>
                                </select>
                             	</p>
								<p><label for="category_2">Type of Marketing Promotions:</label>
                                <select id="category_2" name="category_2">
                                    <option value="">-- Select a Category --</option>
                                    <option value="Auto">Auto</option>
                                    <option value="Biz Opp - Work From Home">Biz Opp - Work From Home</option>
                                    <option value="Contextual Marketing">Contextual Marketing</option>
                                    <option value="Dating">Dating</option>
                                    <option value="Downloads">Downloads</option>
                                    <option value="Education">Education</option>
                                    <option value="Email Marketing">Email Marketing</option>
                                    <option value="Financial">Financial</option>
                                    <option value="Gambling">Gambling</option>
                                    <option value="Health">Health</option>
                                    <option value="Incentivized">Incentivized</option>
                                    <option value="On-Site Placement Marketing">On-Site Placement Marketing</option>
                                    <option value="Other">Other</option>
                                    <option value="Search Engine Optimization">Search Engine Optimization</option>
                                    <option value="Short Leads">Short Leads</option>
                                    <option value="Single Email Submission">Single Email Submission</option>
                                    <option value="Survey">Survey</option>
                                    <option value="Trial Offers">Trial Offers</option>
                                    <option value="Webmaster Marketing">Webmaster Marketing</option>
                                    <option value="Well-Branded">Well-Branded</option>
                                    <option value="Zip Only - Hot!">Zip Only - Hot!</option>
                                </select>
                            	</p>
                                <p><label for="category_3">Type of Marketing Promotions:</label>
                                <select name="category_3">
                                    <option value="">-- Select a Category --</option>
                                    <option value="Auto">Auto</option>
                                    <option value="Biz Opp - Work From Home">Biz Opp - Work From Home</option>
                                    <option value="Contextual Marketing">Contextual Marketing</option>
                                    <option value="Dating">Dating</option>
                                    <option value="Downloads">Downloads</option>
                                    <option value="Education">Education</option>
                                    <option value="Email Marketing">Email Marketing</option>
                                    <option value="Financial">Financial</option>
                                    <option value="Gambling">Gambling</option>
                                    <option value="Health">Health</option>
                                    <option value="Incentivized">Incentivized</option>
                                    <option value="On-Site Placement Marketing">On-Site Placement Marketing</option>
                                    <option value="Other">Other</option>
                                    <option value="Search Engine Optimization">Search Engine Optimization</option>
                                    <option value="Short Leads">Short Leads</option>
                                    <option value="Single Email Submission">Single Email Submission</option>
                                    <option value="Survey">Survey</option>
                                    <option value="Trial Offers">Trial Offers</option>
                                    <option value="Webmaster Marketing">Webmaster Marketing</option>
                                    <option value="Well-Branded">Well-Branded</option>
                                    <option value="Zip Only - Hot!">Zip Only - Hot!</option>
                                </select>
                                </p>
                                <p><label for="comments">Comments:</label><br /><textarea id="comments" name="comments" rows="5" cols="15"></textarea>
                                </p>
                            </div>
                            
                        </div>
                        <!-- end twocol -->
                        
                        <!-- begin onecol -->
                        <div class="onecol">
                            <p class="txtctr">Please read the <a href="publishers_agreement.php">Terms of Agreement</a>.</p>
                            <p><input class="checkbox" type="checkbox" name="agree_terms"> By checking this box, I affirm that I have read, understand and agree to all terms.</p>
                            <p class="txtctr"><input type="submit" value="Send in my application" /></p>
                    	</div>
                        <!-- end onecol -->
                        
                 	</form>   
                </div>
                <!-- end content_inner -->
            </div>
            <!-- end content_bot -->
        </div>
        <!-- end content -->
        
        <!-- begin feature -->
        <div id="feature">
        	<a href=""><img class="silverinet" src="images/silverinet.gif" width="197" height="66" alt="Silver iNET" /></a>
            <img class="fltright" src="http://www.pic3400.com/silverinet/mainfooter/feature-offers.jpg" width="554" height="69" alt="featured offers" usemap="#Map" />
			<map name="Map" id="Map">
            	<!--<area shape="rect" coords="13,27,143,60" href="#1" alt="gamefly" />
				<area shape="rect" coords="147,27,261,60" href="#2" alt="bejewled" />
				<area shape="rect" coords="268,11,322,64" href="#3" alt="vip" />
				<area shape="rect" coords="334,20,437,61" href="#4" alt="zoobooks" />
				<area shape="rect" coords="442,20,550,59" href="#5" alt="torchmark" />-->
			</map>
        </div>
        <!-- end feature -->
        
        <!-- begin footer -->
        <div id="footer">
        	<ul id="footer_nav">
            	<li><a href="index.php">Home</a> | </li>
                <li><a href="about_us.php">About Us</a> | </li>
                <li><a href="affiliate_sign_up.php">Affiliate Sign Up</a> | </li>
                <li><a href="advertiser_sign_up.php">Advertiser Sign Up</a> | </li>
                <li><a href="contact_us.php">Contact Us</a> | </li>
                <li><a href="privacy_policy.php">Privacy Policy</a> | </li>
                <li><a href="publishers_agreement.php">Publishers Agreement</a></li>
            </ul>
        </div>
        <!-- end footer -->
    </div>
    <!--end wrapper-->
</body>
</html>
