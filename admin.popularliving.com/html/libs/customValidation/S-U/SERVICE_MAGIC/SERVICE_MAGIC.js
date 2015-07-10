//this file is for SERVICE_MAGIC
//
function checkornot(ap,sorn)
{
   if(sorn == true)
   {
		 document.getElementById(ap).style.visibility = 'visible';
		 document.getElementById(ap).style.display = 'block';
		 
   } else 
   {
		 document.getElementById(ap).style.visibility = 'hidden';    
		 document.getElementById(ap).style.display = 'none'; 	
	}
}

function showhide() 
{
var ap ='';
var ShowAny=false;
if(document.getElementById('Service_Magic_bathroom').checked){checkornot('Bathroom',true);ShowAny = (ShowAny || true);}else{checkornot('Bathroom',false);ShowAny = (ShowAny || false);}
if(document.getElementById('Service_Magic_paint').checked){checkornot('Paint',true);ShowAny = (ShowAny || true);}else{checkornot('Paint',false);ShowAny = (ShowAny || false);}
if(document.getElementById('Service_Magic_basement').checked){checkornot('Basement',true);ShowAny = (ShowAny || true);}else{checkornot('Basement',false);ShowAny = (ShowAny || false);}
if(document.getElementById('Service_Magic_int_paint').checked){checkornot('Paint2',true);ShowAny = (ShowAny || true);}else{checkornot('Paint2',false);ShowAny = (ShowAny || false);}
if(document.getElementById('Service_Magic_kitchen').checked){checkornot('Kitchen',true);ShowAny = (ShowAny || true);}else{checkornot('Kitchen',false);ShowAny = (ShowAny || false);}
if(document.getElementById('Service_Magic_roof').checked){checkornot('Roof',true);ShowAny = (ShowAny || true);}else{checkornot('Roof',false);ShowAny = (ShowAny || false);}
if(document.getElementById('Service_Magic_siding').checked){checkornot('Siding',true);ShowAny = (ShowAny || true);}else{checkornot('Siding',false);ShowAny = (ShowAny || false);}
if(document.getElementById('Service_Magic_windows').checked){checkornot('Windows',true);ShowAny = (ShowAny || true);}else{checkornot('Windows',false);ShowAny = (ShowAny || false);}
if(document.getElementById('Service_Magic_refcab').checked){checkornot('Refcab',true);ShowAny = (ShowAny || true);}else{checkornot('Refcab',false);ShowAny = (ShowAny || false);}
if(document.getElementById('Service_Magic_instcab').checked){checkornot('Instcab',true);ShowAny = (ShowAny || true);}else{checkornot('Instcab',false);ShowAny = (ShowAny || false);}
if(document.getElementById('Service_Magic_alarm').checked){checkornot('Alarm',true);ShowAny = (ShowAny || true);}else{checkornot('Alarm',false);ShowAny = (ShowAny || false);}
if(document.getElementById('Service_Magic_maid').checked){checkornot('Maid',true);ShowAny = (ShowAny || true);}else{checkornot('Maid',false);ShowAny = (ShowAny || false);}
					
if(ShowAny)
  	{
		 document.getElementById('Contact').style.visibility = 'visible';
		 document.getElementById('Contact').style.display = 'block';
		 document.getElementById('Contact2').style.visibility = 'visible';
		 document.getElementById('Contact2').style.display = 'block';
   } else 
   {
		 document.getElementById('Contact').style.visibility = 'hidden';    
		 document.getElementById('Contact').style.display = 'none'; 	
		 document.getElementById('Contact2').style.visibility = 'hidden';    
		 document.getElementById('Contact2').style.display = 'none'; 
	}

}

function XMLWriter()
{
    this.XML=[];
    this.Nodes=[];
    this.State="";
    this.FormatXML = function(Str)
    {
        if (Str)
            return Str.replace(/&/g, "&amp;").replace(/\"/g, "&quot;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
        return ""
    }
    this.BeginNode = function(Name)
    {
        if (!Name) return;
        if (this.State=="beg") this.XML.push(">");
        this.State="beg";
        this.Nodes.push(Name);
        this.XML.push("<"+Name);
    }
    this.EndNode = function()
    {
        if (this.State=="beg")
        {
            this.XML.push("/>");
            this.Nodes.pop();
        }
        else if (this.Nodes.length>0)
            this.XML.push("</"+this.Nodes.pop()+">");
        this.State="";
    }
    this.Attrib = function(Name, Value)
    {
        if (this.State!="beg" || !Name) return;
        this.XML.push(" "+Name+"=\""+this.FormatXML(Value)+"\"");
    }
    this.WriteString = function(Value)
    {
        if (this.State=="beg") this.XML.push(">");
        this.XML.push(this.FormatXML(Value));
        this.State="";
    }
    this.Node = function(Name, Value)
    {
        if (!Name) return;
        if (this.State=="beg") this.XML.push(">");
        this.XML.push((Value=="" || !Value)?"<"+Name+"/>":"<"+Name+">"+this.FormatXML(Value)+"</"+Name+">");
        this.State="";
    }
	
    this.WriteCData = function(Value)
    {
        if (this.State=="beg") this.XML.push(">");
        this.XML.push("<![CDATA["+this.FormatXML(Value)+"]]>");
        this.State="";
    }	

    this.Close = function()
    {
        while (this.Nodes.length>0)
            this.EndNode();
        this.State="closed";
    }
    this.ToString = function(){return this.XML.join("");}
}
 
function makexml()
{ 
 if(document.getElementById("Service_Magic_bathroom").checked)
 {
  //alert("in process bath");
///////////////////////for bath///////////////////////////////////////////////////////////////
					    var bathFile;
						//string bathFile = Regex.Replace(this.IPAddress, ".", "") + "_bath";
						//string bathFile = "9326 " + gid.ToString().Replace("-", "") + "_bath";//Regex.Replace(this.IPAddress, ".", "");
						var bathwriter = new XMLWriter() ;
			
						//Use automatic indentation for readability.
						//bathwriter.Formatting = Formatting.Indented;
				    
						//Write the ProcessingInstruction node. 
						bathFile = "<?xml version='1.0' encoding='UTF-8'?>";
	                
						//Write the DocumentType node.
						bathFile += "<!DOCTYPE serviceRequest SYSTEM 'http://www.servicemagic.com/dtd/submitServiceRequest.dtd'>";
						//bathwriter.WriteDocType("serviceRequest", null , "http://www.servicemagic.com/dtd/submitServiceRequest.dtd", null);
				        
						//Write the root element
						bathwriter.BeginNode("serviceRequest");
				    
						//Add an attribute to the previously created element 
						bathwriter.Attrib("affiliateCode", "silvercarrot3");
						bathwriter.Attrib("version", "1.0");
						bathwriter.Attrib("testOnly", "false");
				    
						//Start an element
						bathwriter.BeginNode("customer");
						bathwriter.BeginNode("contact");
				    
						bathwriter.BeginNode("firstName");
						
						bathwriter.WriteCData(sFirst);
						bathwriter.EndNode();  // end firstName
						bathwriter.BeginNode("lastName");
						bathwriter.WriteCData(sLast);
						bathwriter.EndNode();  // end lastName
						
						arbathContact =document.getElementById("SERVICE_MAGIC_preferredContactTime").value.split('|'); 		
						bathwriter.BeginNode("contactTime");
						bathwriter.WriteCData(arbathContact[1]);
						bathwriter.EndNode();  // end contactTime
				    
						bathwriter.BeginNode("contactMethods");
				    
						bathwriter.BeginNode("contactMethod");
						bathwriter.Attrib("type", "dayPhone");
						bathwriter.WriteCData(sPhone);
						bathwriter.EndNode();  // end contactMethod
						if (document.getElementById("SERVICE_MAGIC_cellphone").value)
						{
							bathwriter.BeginNode("contactMethod");
							bathwriter.Attrib("type", "cellPhone");
							bathwriter.WriteCData(document.getElementById("SERVICE_MAGIC_cellphone").value);
							bathwriter.EndNode();  // end contactMethod
						}
						if (document.getElementById("SERVICE_MAGIC_eveningphone").value)
						{
							bathwriter.BeginNode("contactMethod");
							bathwriter.Attrib("type", "eveningPhone");
							bathwriter.WriteCData(document.getElementById("SERVICE_MAGIC_eveningphone").value);
							bathwriter.EndNode();  // end contactMethod
						}
						bathwriter.BeginNode("contactMethod");
						bathwriter.Attrib("type", "email");
						bathwriter.WriteCData(sEmail);
						bathwriter.EndNode();  // end contactMethod
				    
						bathwriter.EndNode();  // end contactMethods
						bathwriter.EndNode();  // end contact
				    
						bathwriter.BeginNode("location"); 
						//add sub-elements
						bathwriter.BeginNode("addressLine1");
						bathwriter.WriteCData(sAddress+sAddress2);
						bathwriter.EndNode();  // end addressLine1
						bathwriter.BeginNode("city");
						bathwriter.WriteCData(sCity);
						bathwriter.EndNode();  // end city
						bathwriter.BeginNode("zip");
						bathwriter.WriteCData(sZip);
						bathwriter.EndNode();  // end zip
				    
						bathwriter.EndNode();  // end location
						bathwriter.EndNode();  // end customer
	
						bathwriter.BeginNode("task");
						//Add an attribute to the previously created element 
						bathwriter.Attrib("description", "Bathroom Remodel");
						bathwriter.Attrib("oid", "40129");
				    
						bathwriter.BeginNode("interview");
				    
						bathwriter.BeginNode("question");
						bathwriter.Attrib("id", "90000");
						bathwriter.Attrib("description", "Request Stage");
						bathwriter.Attrib("type", "TASK_INTERVIEW");
						
						arbathStage =document.getElementById("SERVICE_MAGIC_bath_status").value.split('|'); 			    
						bathwriter.BeginNode("answer");
						bathwriter.Attrib("id", arbathStage[0]);
						bathwriter.Attrib("questionID", "90000");
						bathwriter.WriteCData(arbathStage[1]);
						bathwriter.EndNode();  // end answer
						bathwriter.EndNode();  // end question
				    
						bathwriter.BeginNode("question");
						bathwriter.Attrib("id", "160808");
						bathwriter.Attrib("description", "Extensiveness of Remodel");
						bathwriter.Attrib("type", "TASK_INTERVIEW"); 		
							    
						arbathRemodel =document.getElementById("SERVICE_MAGIC_bath_remodel").value.split('|'); 			    
						bathwriter.BeginNode("answer");
						bathwriter.Attrib("id", arbathRemodel[0]);
						bathwriter.Attrib("questionID", "160808");
						bathwriter.WriteCData(arbathRemodel[1]);
						bathwriter.EndNode();  // end answer
						bathwriter.EndNode();  // end question
				    
						bathwriter.BeginNode("question");
						bathwriter.Attrib("id", "4411");
						bathwriter.Attrib("description", "Design Preparation");
						bathwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						arbathDesign =document.getElementById("SERVICE_MAGIC_bath_design_type").value.split('|'); 			    
						bathwriter.BeginNode("answer");
						bathwriter.Attrib("id", arbathDesign[0]);
						bathwriter.Attrib("questionID", "4411");
						bathwriter.WriteCData(arbathDesign[1]);
						bathwriter.EndNode();  // end answer
						bathwriter.EndNode();  // end question
				    
						bathwriter.BeginNode("question");
						bathwriter.Attrib("id", "80000");
						bathwriter.Attrib("description", "Desired Completion Date");
						bathwriter.Attrib("type", "TASK_INTERVIEW"); 	
								
						arbathComp =document.getElementById("SERVICE_MAGIC_bath_completed").value.split('|'); 			    
						bathwriter.BeginNode("answer");
						bathwriter.Attrib("id", arbathComp[0]);
						bathwriter.Attrib("questionID", "80000");
						bathwriter.WriteCData(arbathComp[1]);
						bathwriter.EndNode();  // end answer
						bathwriter.EndNode();  // end question
				    
						bathwriter.BeginNode("question");
						bathwriter.Attrib("id", "70000");
						bathwriter.Attrib("description", "Financing Requested");
						bathwriter.Attrib("type", "TASK_INTERVIEW"); 			 
						
						arbathFin =document.getElementById("SERVICE_MAGIC_bath_financing").value.split('|'); 			     
						bathwriter.BeginNode("answer");
						bathwriter.Attrib("id", arbathFin[0]);
						bathwriter.Attrib("questionID", "70000");
						bathwriter.WriteCData(arbathFin[1]);
						bathwriter.EndNode();  // end answer
						bathwriter.EndNode();  // end question
				    
	
						///test if there is a checkbox been selected		    
						bathFeat = 0;
						for(a=0;a<13;a++)
						{
								teststring = "SERVICE_MAGIC_bath_changed_features_"+a;
								bathFeat |=document.getElementById(teststring).checked; 
						}
						///bathFeat > 0 means there is a check box been checked
						if (bathFeat != 0)
						{
							bathwriter.BeginNode("question");
							bathwriter.Attrib("id", "160809");
							bathwriter.Attrib("description", "Features to be Remodeled");
							bathwriter.Attrib("type", "TASK_INTERVIEW"); 		
							
							for(a=0;a<13;a++)
							{				
								teststring = "SERVICE_MAGIC_bath_changed_features_"+a;	
								if(document.getElementById(teststring).checked)
								{
								arbathFeat =document.getElementById(teststring).value.split('|');			    
								bathwriter.BeginNode("answer");
								bathwriter.Attrib("id", arbathFeat[0]);
								bathwriter.Attrib("questionID", "160809");
								bathwriter.WriteCData(arbathFeat[1]);
								bathwriter.EndNode();  // end answer  
								}
							}//end for
							bathwriter.EndNode();  // end question
						}
				    
						bathwriter.BeginNode("question");
						bathwriter.Attrib("id", "1700");
						bathwriter.Attrib("description", "Consumer Owns Home");
						bathwriter.Attrib("type", "TASK_INTERVIEW"); 		
											
						arbthOwn='';
						for(a=0;a<2;a++)
						{
								teststring = "SERVICE_MAGIC_bath_own_home_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arbathOwn =document.getElementById(teststring).value.split('|'); 
								}
						}
						bathwriter.BeginNode("answer");
						bathwriter.Attrib("id", arbathOwn[0]);
						bathwriter.Attrib("questionID", "1700");
						bathwriter.WriteCData(arbathOwn[1]);
						bathwriter.EndNode();  // end answer
						bathwriter.EndNode();  // end question
				    
						bathwriter.BeginNode("question");
						bathwriter.Attrib("id", "160804");
						bathwriter.Attrib("description", "Requires Work on Historical Structure");
						bathwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						arbathHist='';
						for(a=0;a<2;a++)
						{
								teststring = "SERVICE_MAGIC_bath_historical_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arbathHist =document.getElementById(teststring).value.split('|'); 
								}
						}
						bathwriter.BeginNode("answer");
						bathwriter.Attrib("id", arbathHist[0]);
						bathwriter.Attrib("questionID", "160804");
						bathwriter.WriteCData(arbathHist[1]);
						bathwriter.EndNode();  // end answer
						bathwriter.EndNode();  // end question
				    
						bathwriter.BeginNode("question");
						bathwriter.Attrib("id", "160807");
						bathwriter.Attrib("description", "Covered by Insurance");
						bathwriter.Attrib("type", "TASK_INTERVIEW"); 	
												
						arbathIns='';
						for(a=0;a<2;a++)
						{
								teststring = "SERVICE_MAGIC_bath_insurance_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arbathIns =document.getElementById(teststring).value.split('|'); 
								}
						}									    
						bathwriter.BeginNode("answer");
						bathwriter.Attrib("id", arbathIns[0]);
						bathwriter.Attrib("questionID", "160807");
						bathwriter.WriteCData(arbathIns[1]);
						bathwriter.EndNode();  // end answer
						bathwriter.EndNode();  // end question
				    
						bathwriter.BeginNode("question");
						bathwriter.Attrib("id", "160805");
						bathwriter.Attrib("description", "Request for Commercial Location");
						bathwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						arbathComm='';
						for(a=0;a<2;a++)
						{
								teststring = "SERVICE_MAGIC_bath_commercial_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arbathComm =document.getElementById(teststring).value.split('|'); 
								}
						}				    
						bathwriter.BeginNode("answer");
						bathwriter.Attrib("id", arbathComm[0]);
						bathwriter.Attrib("questionID", "160805");
						bathwriter.WriteCData(arbathComm[1]);
						bathwriter.EndNode();  // end answer
						bathwriter.EndNode();  // end question
				    
						//End the interview element
						bathwriter.EndNode();  // end interview
				    
						//End the task element
						bathwriter.EndNode();  // end task
				    
						// end the root element
						bathwriter.EndNode(); //end serviceRequest
				    
						//bathwriter.ToString();         
						//Write the XML to file and close the writer
						bathwriter.Close();  
						//document.getElementById("ExampleOutput").value=(bathFile+bathwriter.ToString().replace(/</g,"\n<"));
						xmlfiles = (bathFile+bathwriter.ToString().replace(/</g,"\n<"));
						document.getElementById("SERVICE_MAGIC_bathroom_XML").value= xmlfiles;
						//xmlfiles = "xmlfile=" + xmlfiles;
						//document.getElementById("ExampleOutput").value= xmlfiles;		
						//alert(document.getElementById("SERVICE_MAGIC_XMLbath").value);				
			////////////////////////try post it
			// Error: uncaught exception: Permission denied to call method XMLHttpRequest.open


   	  //alert("out process bath");					
}//endif

if(document.getElementById("Service_Magic_paint").checked){						
//alert("in process paint");
///////////////////////for paint///////////////////////////////////////////////////////////////
						var paintFile;
						//string bathFile = Regex.Replace(this.IPAddress, ".", "") + "_bath";
						//string bathFile = "9326 " + gid.ToString().Replace("-", "") + "_bath";//Regex.Replace(this.IPAddress, ".", "");
						var paintwriter = new XMLWriter() ;
						//string paintFile = Regex.Replace(this.IPAddress, ".", "") + "_paint";
						//string paintFile = "9326 " + gid.ToString().Replace("-", "") + "_paint";//Regex.Replace(this.IPAddress, ".", "");
						//XmlTextWriter paintwriter = new XmlTextWriter(AppDomain.CurrentDomain.BaseDirectory+ paintFile+".xml", null);
			
						//Use automatic indentation for readability.
						//paintwriter.Formatting = Formatting.Indented;
			    
						//Write the ProcessingInstruction node. 
						paintFile = "<?xml version='1.0' encoding='UTF-8'?>";
	                
						//Write the DocumentType node.
						paintFile += "<!DOCTYPE serviceRequest SYSTEM 'http://www.servicemagic.com/dtd/submitServiceRequest.dtd'>";
						//bathwriter.WriteDocType("serviceRequest", null , "http://www.servicemagic.com/dtd/submitServiceRequest.dtd", null);
	
                
						//Write the DocumentType node.
						//paintwriter.WriteDocType("serviceRequest", null , "http://www.servicemagic.com/dtd/submitServiceRequest.dtd", null);
				        
						//Write the root element
						paintwriter.BeginNode("serviceRequest");
				    
						//Add an attribute to the previously created element 
						paintwriter.Attrib("affiliateCode", "silvercarrot3");
						paintwriter.Attrib("version", "1.0");
						paintwriter.Attrib("testOnly", "false");
				    
						//Start an element
						paintwriter.BeginNode("customer");
						paintwriter.BeginNode("contact");
				    
						paintwriter.BeginNode("firstName");
						paintwriter.WriteCData(sFirst);
						paintwriter.EndNode();  // end firstName
						paintwriter.BeginNode("lastName");
						paintwriter.WriteCData(sLast);
						paintwriter.EndNode();  // end lastName
						
						arpaintContact = document.getElementById("SERVICE_MAGIC_preferredContactTime").value.split('|');
						paintwriter.BeginNode("contactTime");
						paintwriter.WriteCData(arpaintContact[1]);
						paintwriter.EndNode();  // end contactTime
				    
						paintwriter.BeginNode("contactMethods");
				    
						paintwriter.BeginNode("contactMethod");
						paintwriter.Attrib("type", "dayPhone");
						paintwriter.WriteCData(sPhone);
						paintwriter.EndNode();  // end contactMethod
						if (document.getElementById("SERVICE_MAGIC_cellphone").value)
						{
							paintwriter.BeginNode("contactMethod");
							paintwriter.Attrib("type", "cellPhone");
							paintwriter.WriteCData(document.getElementById("SERVICE_MAGIC_cellphone").value);
							paintwriter.EndNode();  // end contactMethod
						}
						if (document.getElementById("SERVICE_MAGIC_eveningphone").value)
						{
							paintwriter.BeginNode("contactMethod");
							paintwriter.Attrib("type", "eveningPhone");
							paintwriter.WriteCData(document.getElementById("SERVICE_MAGIC_eveningphone").value);
							paintwriter.EndNode();  // end contactMethod
						}
						paintwriter.BeginNode("contactMethod");
						paintwriter.Attrib("type", "email");
						paintwriter.WriteCData(sEmail);
						paintwriter.EndNode();  // end contactMethod
				    
						paintwriter.EndNode();  // end contactMethods
						paintwriter.EndNode();  // end contact
				    
						paintwriter.BeginNode("location"); 
						//add sub-elements
						paintwriter.BeginNode("addressLine1");
						paintwriter.WriteCData(sAddress+sAddress2);
						paintwriter.EndNode();  // end addressLine1
						paintwriter.BeginNode("city");
						paintwriter.WriteCData(sCity);
						paintwriter.EndNode();  // end city
						paintwriter.BeginNode("zip");
						paintwriter.WriteCData(sZip);
						paintwriter.EndNode();  // end zip
				    
						paintwriter.EndNode();  // end location
						paintwriter.EndNode();  // end customer
	
						paintwriter.BeginNode("task");
						//Add an attribute to the previously created element 
						paintwriter.Attrib("description", "Exterior Paint");
						paintwriter.Attrib("oid", "40117");
				    
						paintwriter.BeginNode("interview");
				    
						paintwriter.BeginNode("question");
						paintwriter.Attrib("id", "548");
						paintwriter.Attrib("description", "Type of Project");
						paintwriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						arpaintStage='';
						for(a=0;a<4;a++)
						{
								teststring = "SERVICE_MAGIC_paint_project_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arpaintStage =document.getElementById(teststring).value.split('|'); 
								}
						}
						paintwriter.BeginNode("answer");
						paintwriter.Attrib("id", arpaintStage[0]);
						paintwriter.Attrib("questionID", "548");
						paintwriter.WriteCData(arpaintStage[1]);
						paintwriter.EndNode();  // end answer
						paintwriter.EndNode();  // end question
				    
						paintwriter.BeginNode("question");
						paintwriter.Attrib("id", "536");
						paintwriter.Attrib("description", "Stories of Home");
						paintwriter.Attrib("type", "TASK_INTERVIEW"); 			

						arpaintRemodel='';
						for(a=0;a<3;a++)
						{
								teststring = "SERVICE_MAGIC_paint_stories_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arpaintRemodel =document.getElementById(teststring).value.split('|'); 
								}
						}
						paintwriter.BeginNode("answer");
						paintwriter.Attrib("id", arpaintRemodel[0]);
						paintwriter.Attrib("questionID", "536");
						paintwriter.WriteCData(arpaintRemodel[1]);
						paintwriter.EndNode();  // end answer
						paintwriter.EndNode();  // end question
				    	
						
						paintDesign = 0;
						for(a=0;a<12;a++)
						{
								teststring = "SERVICE_MAGIC_paint_changed_features_"+a;
								paintDesign |=document.getElementById(teststring).checked; 
						}
						
						if (paintDesign != 0)
						{
							paintwriter.BeginNode("question");
							paintwriter.Attrib("id", "5436");
							paintwriter.Attrib("description", "Types of Surfaces");
							paintwriter.Attrib("type", "TASK_INTERVIEW");
							
						    	for(a=0;a<12;a++)
								{
								teststring = "SERVICE_MAGIC_paint_changed_features_"+a;
								if(paintDesign |=document.getElementById(teststring).checked) 
									{
									arpaintDesign = document.getElementById(teststring).value.split('|');
									paintwriter.BeginNode("answer");
									paintwriter.Attrib("id", arpaintDesign[0]);
									paintwriter.Attrib("questionID", "5436");
									paintwriter.WriteCData(arpaintDesign[1]);
									paintwriter.EndNode();  // end answer  
									}
								}
							paintwriter.EndNode();  // end question
						}
				    
						paintwriter.BeginNode("question");
						paintwriter.Attrib("id", "90000");
						paintwriter.Attrib("description", "Request Stage");
						paintwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						arpaintStatus = document.getElementById("SERVICE_MAGIC_paint_status").value.split('|');	        
						paintwriter.BeginNode("answer");
						paintwriter.Attrib("id", arpaintStatus[0]);
						paintwriter.Attrib("questionID", "90000");
						paintwriter.WriteCData(arpaintStatus[1]);
						paintwriter.EndNode();  // end answer
						paintwriter.EndNode();  // end question
				    
						paintwriter.BeginNode("question");
						paintwriter.Attrib("id", "80000");
						paintwriter.Attrib("description", "Desired Completion Date");
						paintwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						arpaintComp = document.getElementById("SERVICE_MAGIC_paint_completed").value.split('|');	        							    
						paintwriter.BeginNode("answer");
						paintwriter.Attrib("id", arpaintComp[0]);
						paintwriter.Attrib("questionID", "80000");
						paintwriter.WriteCData(arpaintComp[1]);
						paintwriter.EndNode();  // end answer
						paintwriter.EndNode();  // end question
				    
						paintwriter.BeginNode("question");
						paintwriter.Attrib("id", "544");
						paintwriter.Attrib("description", "New Paint Color Compared to Old Color");
						paintwriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						arpaintFeat='';
						for(a=0;a<3;a++)
						{
								teststring = "SERVICE_MAGIC_paint_stain_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arpaintFeat =document.getElementById(teststring).value.split('|'); 
								}
						}						
						paintwriter.BeginNode("answer");
						paintwriter.Attrib("id", arpaintFeat[0]);
						paintwriter.Attrib("questionID", "544");
						paintwriter.WriteCData(arpaintFeat[1]);
						paintwriter.EndNode();  // end answer
						paintwriter.EndNode();  // end question
				    
						paintwriter.BeginNode("question");
						paintwriter.Attrib("id", "70000");
						paintwriter.Attrib("description", "Financing Requested");
						paintwriter.Attrib("type", "TASK_INTERVIEW"); 	

						arpaintFin = document.getElementById("SERVICE_MAGIC_paint_financing").value.split('|');	        							    
						paintwriter.BeginNode("answer");
						paintwriter.Attrib("id", arpaintFin[0]);
						paintwriter.Attrib("questionID", "70000");
						paintwriter.WriteCData(arpaintFin[1]);
						paintwriter.EndNode();  // end answer
						paintwriter.EndNode();  // end question
				    
						paintwriter.BeginNode("question");
						paintwriter.Attrib("id", "30000");
						paintwriter.Attrib("description", "Historical Work");
						paintwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var paintHist = "No";
						var paintHistID = "10002";
						if (document.getElementById("SERVICE_MAGIC_paint_financing").checked)
						{
							paintHist = "Yes";
							paintHistID = "10001";
						}
						//string[] arpaintHist = new string[1];
						//arpaintHist = paintHist.Split(splitter);
						paintwriter.BeginNode("answer");
						paintwriter.Attrib("id", paintHistID);
						paintwriter.Attrib("questionID", "30000");
						paintwriter.WriteCData(paintHist);
						paintwriter.EndNode();  // end answer
						paintwriter.EndNode();  // end question
				    
						paintwriter.BeginNode("question");
						paintwriter.Attrib("id", "545");
						paintwriter.Attrib("description", "Years Since House Was Painted");
						paintwriter.Attrib("type", "TASK_INTERVIEW"); 	

						arpaintExt='';
						for(a=0;a<4;a++)
						{
								teststring = "SERVICE_MAGIC_paint_exterior_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arpaintExt =document.getElementById(teststring).value.split('|'); 
								}
						}						
						paintwriter.BeginNode("answer");
						paintwriter.Attrib("id", arpaintExt[0]);
						paintwriter.Attrib("questionID", "545");
						paintwriter.WriteCData(arpaintExt[1]);
						paintwriter.EndNode();  // end answer
						paintwriter.EndNode();  // end question
				    
					    paintSign = 0;
						for(a=0;a<9;a++)
						{
								teststring = "SERVICE_MAGIC_paint_signs_"+a;
								paintSign |=document.getElementById(teststring).checked; 
						}
						
						if (paintSign != 0)
						{
							paintwriter.BeginNode("question");
							paintwriter.Attrib("id", "5437");
							paintwriter.Attrib("description", "Problems");
							paintwriter.Attrib("type", "TASK_INTERVIEW"); 	
							for(a=0;a<9;a++)
							{	  
								teststring = "SERVICE_MAGIC_paint_signs_"+a;
								arpaintSign |=document.getElementById(teststring).value.split('|');

								paintwriter.BeginNode("answer");
								paintwriter.Attrib("id", arpaintSign[0]);
								paintwriter.Attrib("questionID", "5437");
								paintwriter.WriteCData(arpaintSign[1]);
								paintwriter.EndNode();  // end answer  
							}
							paintwriter.EndNode();  // end question
						}
				    
						paintwriter.BeginNode("question");
						paintwriter.Attrib("id", "10019");
						paintwriter.Attrib("description", "Request for Commercial Location");
						paintwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var paintComm = "No";
						var paintCommID = "10002";
						if (document.getElementById("SERVICE_MAGIC_paint_commercial").checked)
						{
							paintComm = "Yes";
							paintCommID = "10001";
						}
						//string[] arpaintComm = new string[1];
						//arpaintComm = paintComm.Split(splitter);
						paintwriter.BeginNode("answer");
						paintwriter.Attrib("id", paintCommID);
						paintwriter.Attrib("questionID", "10019");
						paintwriter.WriteCData(paintComm);
						paintwriter.EndNode();  // end answer
						paintwriter.EndNode();  // end question
				    
						paintwriter.BeginNode("question");
						paintwriter.Attrib("id", "5739");
						paintwriter.Attrib("description", "Scope of Project");
						paintwriter.Attrib("type", "TASK_INTERVIEW"); 			
						    
						var paintSF = document.getElementById("SERVICE_MAGIC_paint_square_footage").value;
						var paintPct = document.getElementById("SERVICE_MAGIC_paint_percent").value;
						paintwriter.BeginNode("answer");
						paintwriter.Attrib("id", "5997");
						paintwriter.Attrib("questionID", "5739");
						paintwriter.WriteCData(paintSF);
						paintwriter.EndNode();  // end answer
						paintwriter.BeginNode("answer");
						paintwriter.Attrib("id", "4638");
						paintwriter.Attrib("questionID", "5739");
						paintwriter.WriteCData(paintPct);
						paintwriter.EndNode();  // end answer
						paintwriter.EndNode();  // end question
				    
						paintwriter.BeginNode("question");
						paintwriter.Attrib("id", "1700");
						paintwriter.Attrib("description", "Consumer Owns Home");
						paintwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var paintOwn = "No";
						var paintOwnID = "10002";
						if (document.getElementById("SERVICE_MAGIC_paint_own_home").checked)
						{
							paintOwn = "Yes";
							paintOwnID = "10001";
						}
						//string[] arpaintComm = new string[1];
						//arpaintComm = paintComm.Split(splitter);
						paintwriter.BeginNode("answer");
						paintwriter.Attrib("id", paintOwnID);
						paintwriter.Attrib("questionID", "1700");
						paintwriter.WriteCData(paintOwn);
						paintwriter.EndNode();  // end answer
						paintwriter.EndNode();  // end question
				    
						//End the interview element
						paintwriter.EndNode();  // end interview
				    
						//End the task element
						paintwriter.EndNode();  // end task
				    
						// end the root element
						paintwriter.EndNode();
				    
						//paintwriter.Flush();         
						//Write the XML to file and close the writer
						paintwriter.Close();
						xmlfiles=(paintFile+paintwriter.ToString().replace(/</g,"\n<"));
						//document.getElementById("ExampleOutput").value= xmlfiles;  
						document.getElementById("SERVICE_MAGIC_paint_XML").value=xmlfiles;
						
						
}//endif
if(document.getElementById("Service_Magic_int_paint").checked){						
///////////////////////for intpaint///////////////////////////////////////////////////////////////
						var intpaintFile;
						//string bathFile = Regex.Replace(this.IPAddress, ".", "") + "_bath";
						//string bathFile = "9326 " + gid.ToString().Replace("-", "") + "_bath";//Regex.Replace(this.IPAddress, ".", "");
						var intpaintwriter = new XMLWriter() ;
						//string paintFile = Regex.Replace(this.IPAddress, ".", "") + "_paint";
						//string paintFile = "9326 " + gid.ToString().Replace("-", "") + "_paint";//Regex.Replace(this.IPAddress, ".", "");
						//XmlTextWriter paintwriter = new XmlTextWriter(AppDomain.CurrentDomain.BaseDirectory+ paintFile+".xml", null);
			
						//Use automatic indentation for readability.
						//paintwriter.Formatting = Formatting.Indented;
			    
						//Write the ProcessingInstruction node. 
						intpaintFile = "<?xml version='1.0' encoding='UTF-8'?>";
	                
						//Write the DocumentType node.
						intpaintFile += "<!DOCTYPE serviceRequest SYSTEM 'http://www.servicemagic.com/dtd/submitServiceRequest.dtd'>";
						//bathwriter.WriteDocType("serviceRequest", null , "http://www.servicemagic.com/dtd/submitServiceRequest.dtd", null);
					        
						//Write the root element
						intpaintwriter.BeginNode("serviceRequest");
				    
						//Add an attribute to the previously created element 
						intpaintwriter.Attrib("affiliateCode", "silvercarrot3");
						intpaintwriter.Attrib("version", "1.0");
						intpaintwriter.Attrib("testOnly", "false");
				    
						//Start an element
						intpaintwriter.BeginNode("customer");
						intpaintwriter.BeginNode("contact");
				    
						intpaintwriter.BeginNode("firstName");
						intpaintwriter.WriteCData(sFirst);
						intpaintwriter.EndNode();  // end firstName
						intpaintwriter.BeginNode("lastName");
						intpaintwriter.WriteCData(sLast);
						intpaintwriter.EndNode();  // end lastName
						
						arintpaintContact = document.getElementById("SERVICE_MAGIC_preferredContactTime").value.split('|');
						intpaintwriter.BeginNode("contactTime");
						intpaintwriter.WriteCData(arintpaintContact[1]);
						intpaintwriter.EndNode();  // end contactTime
				    
						intpaintwriter.BeginNode("contactMethods");
				    
						intpaintwriter.BeginNode("contactMethod");
						intpaintwriter.Attrib("type", "dayPhone");
						intpaintwriter.WriteCData(sPhone);
						intpaintwriter.EndNode();  // end contactMethod
						if (document.getElementById("SERVICE_MAGIC_cellphone").value)
						{
							intpaintwriter.BeginNode("contactMethod");
							intpaintwriter.Attrib("type", "cellPhone");
							intpaintwriter.WriteCData(document.getElementById("SERVICE_MAGIC_cellphone").value);
							intpaintwriter.EndNode();  // end contactMethod
						}
						if (document.getElementById("SERVICE_MAGIC_eveningphone").value)
						{
							intpaintwriter.BeginNode("contactMethod");
							intpaintwriter.Attrib("type", "eveningPhone");
							intpaintwriter.WriteCData(document.getElementById("SERVICE_MAGIC_eveningphone").value);
							intpaintwriter.EndNode();  // end contactMethod
						}
						
						intpaintwriter.BeginNode("contactMethod");
						intpaintwriter.Attrib("type", "email");
						intpaintwriter.WriteCData(sEmail);
						intpaintwriter.EndNode();  // end contactMethod
				    
						intpaintwriter.EndNode();  // end contactMethods
						intpaintwriter.EndNode();  // end contact
				    
						intpaintwriter.BeginNode("location"); 
						//add sub-elements
						intpaintwriter.BeginNode("addressLine1");
						intpaintwriter.WriteCData(sAddress+sAddress2);
						intpaintwriter.EndNode();  // end addressLine1
						intpaintwriter.BeginNode("city");
						intpaintwriter.WriteCData(sCity);
						intpaintwriter.EndNode();  // end city
						intpaintwriter.BeginNode("zip");
						intpaintwriter.WriteCData(sZip);
						intpaintwriter.EndNode();  // end zip
				    
						intpaintwriter.EndNode();  // end location
						intpaintwriter.EndNode();  // end customer
	
						intpaintwriter.BeginNode("task");
						//Add an attribute to the previously created element 
						intpaintwriter.Attrib("description", "Interior Paint");
						intpaintwriter.Attrib("oid", "40118");
				    
						intpaintwriter.BeginNode("interview");
				    
						//intpaintwriter.BeginNode("question"); 
						//intpaintwriter.Attrib("id", "544"); 
						//intpaintwriter.Attrib("description", "New Paint Color Compared to Old Color"); 
						//intpaintwriter.Attrib("type", "TASK_INTERVIEW");  			    
						//string intpaintStage = this.Value("paint2_stain"); 
						//string[] arintpaintStage = new string[1]; 
						//arintpaintStage = intpaintStage.Split(splitter); 
						//intpaintwriter.BeginNode("answer"); 
						//intpaintwriter.Attrib("id", arintpaintStage[0]); 
						//intpaintwriter.Attrib("questionID", "544"); 
						//intpaintwriter.WriteCData(arintpaintStage[1]);  
						//intpaintwriter.EndNode();  // end answer 
						//intpaintwriter.EndNode();  // end question 
				    
						intpaintwriter.BeginNode("question");
						intpaintwriter.Attrib("id", "5235");
						intpaintwriter.Attrib("description", "House Occupancy");
						intpaintwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						arintpaintRemodel='';
						for(a=0;a<3;a++)
						{
								teststring = "SERVICE_MAGIC_paint2_occhouse_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arintpaintRemodel =document.getElementById(teststring).value.split('|'); 
								}
						}							
						intpaintwriter.BeginNode("answer");
						intpaintwriter.Attrib("id", arintpaintRemodel[0]);
						intpaintwriter.Attrib("questionID", "5235");
						intpaintwriter.WriteCData(arintpaintRemodel[1]);
						intpaintwriter.EndNode();  // end answer
						intpaintwriter.EndNode();  // end question
				    
						intpaintwriter.BeginNode("question");
						intpaintwriter.Attrib("id", "5435");
						intpaintwriter.Attrib("description", "Number of Rooms to be Painted");
						intpaintwriter.Attrib("type", "TASK_INTERVIEW");
						 			    
						var intpaintSF = document.getElementById("SERVICE_MAGIC_paint2_rooms").value;
						intpaintwriter.BeginNode("answer");
						intpaintwriter.Attrib("id", "0");
						intpaintwriter.Attrib("questionID", "5435");
						intpaintwriter.WriteCData(intpaintSF);
						intpaintwriter.EndNode();  // end answer
						intpaintwriter.EndNode();  // end question
				    
						var intpaintDesign = document.getElementById("SERVICE_MAGIC_preferredContactTime").value;
						
					    intpaintDesign = 0;
						for(a=0;a<7;a++)
						{
								teststring = "SERVICE_MAGIC_paint2_need_paint_"+a;
								intpaintDesign |=document.getElementById(teststring).checked; 
						}
						
						if (intpaintDesign != 0)
						{
							intpaintwriter.BeginNode("question");
							intpaintwriter.Attrib("id", "551");
							intpaintwriter.Attrib("description", "Rooms to be Painted");
							intpaintwriter.Attrib("type", "TASK_INTERVIEW"); 			    
							for(a=0;a<7;a++)
							{
								teststring = "SERVICE_MAGIC_paint2_need_paint_"+a;
								arintpaintDesign =document.getElementById(teststring).value.split('|');
								
								intpaintwriter.BeginNode("answer");
								intpaintwriter.Attrib("id", arintpaintDesign[0]);
								intpaintwriter.Attrib("questionID", "551");
								intpaintwriter.WriteCData(arintpaintDesign[1]);
								intpaintwriter.EndNode();  // end answer  
							}
							intpaintwriter.EndNode();  // end question
						}
				    
						intpaintwriter.BeginNode("question");
						intpaintwriter.Attrib("id", "90000");
						intpaintwriter.Attrib("description", "Request Stage");
						intpaintwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						arintpaintStatus = document.getElementById("SERVICE_MAGIC_paint2_status").value.split('|');
						intpaintwriter.BeginNode("answer");
						intpaintwriter.Attrib("id", arintpaintStatus[0]);
						intpaintwriter.Attrib("questionID", "90000");
						intpaintwriter.WriteCData(arintpaintStatus[1]);
						intpaintwriter.EndNode();  // end answer
						intpaintwriter.EndNode();  // end question
				    
						intpaintwriter.BeginNode("question");
						intpaintwriter.Attrib("id", "80000");
						intpaintwriter.Attrib("description", "Desired Completion Date");
						intpaintwriter.Attrib("type", "TASK_INTERVIEW"); 
						
						arintpaintComp = document.getElementById("SERVICE_MAGIC_paint2_completed").value.split('|');
						intpaintwriter.BeginNode("answer");
						intpaintwriter.Attrib("id", arintpaintComp[0]);
						intpaintwriter.Attrib("questionID", "80000");
						intpaintwriter.WriteCData(arintpaintComp[1]);
						intpaintwriter.EndNode();  // end answer
						intpaintwriter.EndNode();  // end question
				    
						intpaintwriter.BeginNode("question");
						intpaintwriter.Attrib("id", "559");
						intpaintwriter.Attrib("description", "New Color Compared to Old Color");
						intpaintwriter.Attrib("type", "TASK_INTERVIEW"); 
						
						arintpaintFeat='';
						for(a=0;a<4;a++)
						{
								teststring = "SERVICE_MAGIC_paint2_wall_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arintpaintFeat =document.getElementById(teststring).value.split('|'); 
								}
						}							
						intpaintwriter.BeginNode("answer");
						intpaintwriter.Attrib("id", arintpaintFeat[0]);
						intpaintwriter.Attrib("questionID", "559");
						intpaintwriter.WriteCData(arintpaintFeat[1]);
						intpaintwriter.EndNode();  // end answer
						intpaintwriter.EndNode();  // end question
				    
						intpaintwriter.BeginNode("question");
						intpaintwriter.Attrib("id", "5290");
						intpaintwriter.Attrib("description", "Ceiling Height");
						intpaintwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						arintpaintFin='';
						for(a=0;a<3;a++)
						{
								teststring = "SERVICE_MAGIC_paint2_height_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arintpaintFin =document.getElementById(teststring).value.split('|'); 
								}
						}		    
						intpaintwriter.BeginNode("answer");
						intpaintwriter.Attrib("id", arintpaintFin[0]);
						intpaintwriter.Attrib("questionID", "5290");
						intpaintwriter.WriteCData(arintpaintFin[1]);
						intpaintwriter.EndNode();  // end answer
						intpaintwriter.EndNode();  // end question
				    
						intpaintwriter.BeginNode("question");
						intpaintwriter.Attrib("id", "30000");
						intpaintwriter.Attrib("description", "Historical Work");
						intpaintwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var intpaintHist = "No";
						var intpaintHistID = "10002";
						if (document.getElementById("SERVICE_MAGIC_paint2_hist_struct").checked)
						{
							intpaintHist = "Yes";
							intpaintHistID = "10001";
						}
						//string[] arintpaintHist = new string[1];
						//arintpaintHist = intpaintHist.Split(splitter);
						intpaintwriter.BeginNode("answer");
						intpaintwriter.Attrib("id", intpaintHistID);
						intpaintwriter.Attrib("questionID", "30000");
						intpaintwriter.WriteCData(intpaintHist);
						intpaintwriter.EndNode();  // end answer
						intpaintwriter.EndNode();  // end question

					    intpaintSign = 0;
						for(a=0;a<8;a++)
						{
								teststring = "SERVICE_MAGIC_paint2_items_"+a;
								intpaintSign |=document.getElementById(teststring).checked; 
						}

						if (intpaintSign != 0)
						{
							intpaintwriter.BeginNode("question");
							intpaintwriter.Attrib("id", "5236");
							intpaintwriter.Attrib("description", "Items to be Painted or Stained");
							intpaintwriter.Attrib("type", "TASK_INTERVIEW"); 			    
							for(a=0;a<8;a++)
							{
								teststring = "SERVICE_MAGIC_paint2_items_"+a;
								arintpaintSign =document.getElementById(teststring).value.split('|');

								intpaintwriter.BeginNode("answer");
								intpaintwriter.Attrib("id", arintpaintSign[0]);
								intpaintwriter.Attrib("questionID", "5236");
								intpaintwriter.WriteCData(arintpaintSign[1]);
								intpaintwriter.EndNode();  // end answer  
							}
							intpaintwriter.EndNode();  // end question
						}
						
					    intpaintSpecial = 0;
						for(a=0;a<8;a++)
						{
								teststring = "SERVICE_MAGIC_paint2_special_"+a;
								intpaintSpecial |=document.getElementById(teststring).checked; 
						}
						if (intpaintSpecial != 0)
						{
							intpaintwriter.BeginNode("question");
							intpaintwriter.Attrib("id", "5401");
							intpaintwriter.Attrib("description", "Special Circumstances");
							intpaintwriter.Attrib("type", "TASK_INTERVIEW"); 			    
							for(a=0;a<8;a++)
							{
								teststring = "SERVICE_MAGIC_paint2_special_"+a;
								arintpaintSpecial =document.getElementById(teststring).value.split('|');

								intpaintwriter.BeginNode("answer");
								intpaintwriter.Attrib("id", arintpaintSpecial[0]);
								intpaintwriter.Attrib("questionID", "5401");
								intpaintwriter.WriteCData(arintpaintSpecial[1]);
								intpaintwriter.EndNode();  // end answer  
							}
							intpaintwriter.EndNode();  // end question
						}
				    
						intpaintwriter.BeginNode("question");
						intpaintwriter.Attrib("id", "10019");
						intpaintwriter.Attrib("description", "Request for Commercial Location");
						intpaintwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var intpaintComm = "No";
						var intpaintCommID = "10002";

						if (document.getElementById("SERVICE_MAGIC_paint2_commercial").checked)
						{
							intpaintComm = "Yes";
							intpaintCommID = "10001";
						}
						//string[] arintpaintComm = new string[1];
						//arintpaintComm = intpaintComm.Split(splitter);
						intpaintwriter.BeginNode("answer");
						intpaintwriter.Attrib("id", intpaintCommID);
						intpaintwriter.Attrib("questionID", "10019");
						intpaintwriter.WriteCData(intpaintComm);
						intpaintwriter.EndNode();  // end answer
						intpaintwriter.EndNode();  // end question
				    
						intpaintwriter.BeginNode("question");
						intpaintwriter.Attrib("id", "1700");
						intpaintwriter.Attrib("description", "Consumer Owns Home");
						intpaintwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var intpaintOwn = "No";
						var intpaintOwnID = "10002";
						if (document.getElementById("SERVICE_MAGIC_paint2_own_home").checked)
						{
							intpaintOwn = "Yes";
							intpaintOwnID = "10001";
						}
						//string[] arintpaintComm = new string[1];
						//arintpaintComm = intpaintComm.Split(splitter);
						intpaintwriter.BeginNode("answer");
						intpaintwriter.Attrib("id", intpaintOwnID);
						intpaintwriter.Attrib("questionID", "1700");
						intpaintwriter.WriteCData(intpaintOwn);
						intpaintwriter.EndNode();  // end answer
						intpaintwriter.EndNode();  // end question
				    
						//End the interview element
						intpaintwriter.EndNode();  // end interview
				    
						//End the task element
						intpaintwriter.EndNode();  // end task
				    
						// end the root element
						intpaintwriter.EndNode();
				    
						//intpaintwriter.Flush();         
						//Write the XML to file and close the writer
						intpaintwriter.Close();  
						document.getElementById("SERVICE_MAGIC_int_paint_XML").value=(intpaintFile+intpaintwriter.ToString().replace(/</g,"\n<"));					
						
						
}//endif
if(document.getElementById("Service_Magic_roof").checked){					
///////////////////////for INSTALL ROOFING///////////////////////////////////////////////////////////////
	
						var roofFile;
						var roofwriter = new XMLWriter() ;
						roofFile = "<?xml version='1.0' encoding='UTF-8'?>";
						roofFile += "<!DOCTYPE serviceRequest SYSTEM 'http://www.servicemagic.com/dtd/submitServiceRequest.dtd'>";				        
						//Write the root element
						roofwriter.BeginNode("serviceRequest");
				    
						//Add an attribute to the previously created element 
						roofwriter.Attrib("affiliateCode", "silvercarrot3");
						roofwriter.Attrib("version", "1.0");
						roofwriter.Attrib("testOnly", "false");
				    
						//Start an element
						roofwriter.BeginNode("customer");
						roofwriter.BeginNode("contact");
				    
						roofwriter.BeginNode("firstName");
						roofwriter.WriteCData(sFirst);
						roofwriter.EndNode();  // end firstName
						roofwriter.BeginNode("lastName");
						roofwriter.WriteCData(sLast);
						roofwriter.EndNode();  // end lastName
						arroofContact = document.getElementById("SERVICE_MAGIC_preferredContactTime").value.split('|');
						roofwriter.BeginNode("contactTime");
						roofwriter.WriteCData(arroofContact[1]);
						roofwriter.EndNode();  // end contactTime
				    
						roofwriter.BeginNode("contactMethods");
				    
						roofwriter.BeginNode("contactMethod");
						roofwriter.Attrib("type", "dayPhone");
						roofwriter.WriteCData(sPhone);
						roofwriter.EndNode();  // end contactMethod
						if (document.getElementById("SERVICE_MAGIC_cellphone").value)
						{
							roofwriter.BeginNode("contactMethod");
							roofwriter.Attrib("type", "cellPhone");
							roofwriter.WriteCData(document.getElementById("SERVICE_MAGIC_cellphone").value);
							roofwriter.EndNode();  // end contactMethod
						}
						if (document.getElementById("SERVICE_MAGIC_eveningphone").value)
						{
							roofwriter.BeginNode("contactMethod");
							roofwriter.Attrib("type", "eveningPhone");
							roofwriter.WriteCData(document.getElementById("SERVICE_MAGIC_eveningphone").value);
							roofwriter.EndNode();  // end contactMethod
						}

						roofwriter.BeginNode("contactMethod");
						roofwriter.Attrib("type", "email");
						roofwriter.WriteCData(sEmail);
						roofwriter.EndNode();  // end contactMethod
				    
						roofwriter.EndNode();  // end contactMethods
						roofwriter.EndNode();  // end contact
				    
						roofwriter.BeginNode("location"); 
						//add sub-elements
						roofwriter.BeginNode("addressLine1");
						roofwriter.WriteCData(sAddress+sAddress2);
						roofwriter.EndNode();  // end addressLine1
						roofwriter.BeginNode("city");
						roofwriter.WriteCData(sCity);
						roofwriter.EndNode();  // end city
						roofwriter.BeginNode("zip");
						roofwriter.WriteCData(sZip);
						roofwriter.EndNode();  // end zip
				    
						roofwriter.EndNode();  // end location
						roofwriter.EndNode();  // end customer
	
						roofwriter.BeginNode("task");
						//Add an attribute to the previously created element 
						roofwriter.Attrib("description", "Install Roofing");
						roofwriter.Attrib("oid", "40133");
				    
						roofwriter.BeginNode("interview");
				    
						roofwriter.BeginNode("question");
						roofwriter.Attrib("id", "583");
						roofwriter.Attrib("description", "Stories in House");
						roofwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						arroofRemodel = document.getElementById("SERVICE_MAGIC_roof_stories").value.split('|');
						roofwriter.BeginNode("answer");
						roofwriter.Attrib("id", arroofRemodel[0]);
						roofwriter.Attrib("questionID", "583");
						roofwriter.WriteCData(arroofRemodel[1]);
						roofwriter.EndNode();  // end answer
						roofwriter.EndNode();  // end question
				    
						roofwriter.BeginNode("question");
						roofwriter.Attrib("id", "90000");
						roofwriter.Attrib("description", "Request Stage");
						roofwriter.Attrib("type", "TASK_INTERVIEW");
						
						arroofStage = document.getElementById("SERVICE_MAGIC_roof_status").value.split('|');
						roofwriter.BeginNode("answer");
						roofwriter.Attrib("id", arroofStage[0]);
						roofwriter.Attrib("questionID", "90000");
						roofwriter.WriteCData(arroofStage[1]);
						roofwriter.EndNode();  // end answer
						roofwriter.EndNode();  // end question
				    
						roofwriter.BeginNode("question");
						roofwriter.Attrib("id", "580");
						roofwriter.Attrib("description", "Pitch of Roof");
						roofwriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						arroofDesign = document.getElementById("SERVICE_MAGIC_roof_pitch").value.split('|');
						roofwriter.BeginNode("answer");
						roofwriter.Attrib("id", arroofDesign[0]);
						roofwriter.Attrib("questionID", "580");
						roofwriter.WriteCData(arroofDesign[1]);
						roofwriter.EndNode();  // end answer
						roofwriter.EndNode();  // end question
				    
						roofwriter.BeginNode("question");
						roofwriter.Attrib("id", "80000");
						roofwriter.Attrib("description", "Desired Completion Date");
						roofwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						
						arroofComp = document.getElementById("SERVICE_MAGIC_roof_completed").value.split('|');
						roofwriter.BeginNode("answer");
						roofwriter.Attrib("id", arroofComp[0]);
						roofwriter.Attrib("questionID", "80000");
						roofwriter.WriteCData(arroofComp[1]);
						roofwriter.EndNode();  // end answer
						roofwriter.EndNode();  // end question
				    
						roofwriter.BeginNode("question");
						roofwriter.Attrib("id", "70000");
						roofwriter.Attrib("description", "Financing Requested");
						roofwriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						
						arroofFin = document.getElementById("SERVICE_MAGIC_roof_financing").value.split('|');
						roofwriter.BeginNode("answer");
						roofwriter.Attrib("id", arroofFin[0]);
						roofwriter.Attrib("questionID", "70000");
						roofwriter.WriteCData(arroofFin[1]);
						roofwriter.EndNode();  // end answer
						roofwriter.EndNode();  // end question
				    
					    roofFeat = 0;
						for(a=0;a<8;a++)
						{
								teststring = "SERVICE_MAGIC_roof_special_features_"+a;
								roofFeat |=document.getElementById(teststring).checked; 
						}

						if (roofFeat != 0)
						{
							roofwriter.BeginNode("question");
							roofwriter.Attrib("id", "160806");
							roofwriter.Attrib("description", "Special Features for Roof");
							roofwriter.Attrib("type", "TASK_INTERVIEW");
							for(a=0;a<8;a++)
							{
								teststring = "SERVICE_MAGIC_roof_special_features_"+a;
								arroofFeat =document.getElementById(teststring).value.split('|');

								roofwriter.BeginNode("answer");
								roofwriter.Attrib("id", arroofFeat[0]);
								roofwriter.Attrib("questionID", "160806");
								roofwriter.WriteCData(arroofFeat[1]);
								roofwriter.EndNode();  // end answer 
							}
							roofwriter.EndNode();  // end question
						}
				    
					    roofHist = 0;
						for(a=0;a<7;a++)
						{
								teststring = "SERVICE_MAGIC_roof_elements_"+a;
								roofHist |=document.getElementById(teststring).checked; 
						}

						if (roofHist != 0)
						{
							roofwriter.BeginNode("question");
							roofwriter.Attrib("id", "581");
							roofwriter.Attrib("description", "Roof Elements");
							roofwriter.Attrib("type", "TASK_INTERVIEW"); 			    
							for(a=0;a<7;a++)
							{
								teststring = "SERVICE_MAGIC_roof_elements_"+a;
								arroofHist =document.getElementById(teststring).value.split('|');
								
								roofwriter.BeginNode("answer");
								roofwriter.Attrib("id", arroofHist[0]);
								roofwriter.Attrib("questionID", "581");
								roofwriter.WriteCData(arroofHist[1]);
								roofwriter.EndNode();  // end answer 
							}
							roofwriter.EndNode();  // end question
						}
				    
						roofwriter.BeginNode("question");
						roofwriter.Attrib("id", "1700");
						roofwriter.Attrib("description", "Consumer Owns Home");
						roofwriter.Attrib("type", "TASK_INTERVIEW"); 
						
						arroofOwn='';
						for(a=0;a<2;a++)
						{
								teststring = "SERVICE_MAGIC_roof_own_home_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arroofOwn =document.getElementById(teststring).value.split('|'); 
								}
						}								    
						roofwriter.BeginNode("answer");
						roofwriter.Attrib("id", arroofOwn[0]);
						roofwriter.Attrib("questionID", "1700");
						roofwriter.WriteCData(arroofOwn[1]);
						roofwriter.EndNode();  // end answer
						roofwriter.EndNode();  // end question
				    			    
						roofwriter.BeginNode("question");
						roofwriter.Attrib("id", "160807");
						roofwriter.Attrib("description", "Covered by Insurance");
						roofwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						arroofIns='';
						for(a=0;a<2;a++)
						{
								teststring = "SERVICE_MAGIC_roof_insurance_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arroofIns =document.getElementById(teststring).value.split('|'); 
								}
						}								    
						roofwriter.BeginNode("answer");
						roofwriter.Attrib("id", arroofIns[0]);
						roofwriter.Attrib("questionID", "160807");
						roofwriter.WriteCData(arroofIns[1]);
						roofwriter.EndNode();  // end answer
						roofwriter.EndNode();  // end question
				    
						roofwriter.BeginNode("question");
						roofwriter.Attrib("id", "160805");
						roofwriter.Attrib("description", "Request for Commercial Location");
						roofwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						arroofComm='';
						for(a=0;a<2;a++)
						{
								teststring = "SERVICE_MAGIC_roof_commercial_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arroofComm =document.getElementById(teststring).value.split('|'); 
								}
						}						
						roofwriter.BeginNode("answer");
						roofwriter.Attrib("id", arroofComm[0]);
						roofwriter.Attrib("questionID", "160805");
						roofwriter.WriteCData(arroofComm[1]);
						roofwriter.EndNode();  // end answer
						roofwriter.EndNode();  // end question
				    
						//End the interview element
						roofwriter.EndNode();  // end interview
				    
						//End the task element
						roofwriter.EndNode();  // end task
				    
						// end the root element
						roofwriter.EndNode();
				    
						//roofwriter.Flush();         
						//Write the XML to file and close the writer
						roofwriter.Close();  
						document.getElementById("SERVICE_MAGIC_roof_XML").value=(roofFile+roofwriter.ToString().replace(/</g,"\n<"));					
}//endif
if(document.getElementById("Service_Magic_basement").checked){		

///////////////////////for basement remodel///////////////////////////////////////////////////////////////
						var baaseFile;
						var basewriter = new XMLWriter() ;
						baseFile = "<?xml version='1.0' encoding='UTF-8'?>";
						baseFile += "<!DOCTYPE serviceRequest SYSTEM 'http://www.servicemagic.com/dtd/submitServiceRequest.dtd'>";
						//Write the root element
						basewriter.BeginNode("serviceRequest");
				    
						//Add an attribute to the previously created element 
						basewriter.Attrib("affiliateCode", "silvercarrot3");
						basewriter.Attrib("version", "1.0");
						basewriter.Attrib("testOnly", "false");
				    
						//Start an element
						basewriter.BeginNode("customer");
						basewriter.BeginNode("contact");
				    
						basewriter.BeginNode("firstName");
						basewriter.WriteCData(sFirst);
						basewriter.EndNode();  // end firstName
						basewriter.BeginNode("lastName");
						basewriter.WriteCData(sLast);
						basewriter.EndNode();  // end lastName
						arbaseContact = document.getElementById("SERVICE_MAGIC_preferredContactTime").value.split('|');
						basewriter.BeginNode("contactTime");
						basewriter.WriteCData(arbaseContact[1]);
						basewriter.EndNode();  // end contactTime
				    
						basewriter.BeginNode("contactMethods");
				    
						basewriter.BeginNode("contactMethod");
						basewriter.Attrib("type", "dayPhone");
						basewriter.WriteCData(sPhone);
						basewriter.EndNode();  // end contactMethod
						if (document.getElementById("SERVICE_MAGIC_cellphone").value)
						{
							basewriter.BeginNode("contactMethod");
							basewriter.Attrib("type", "cellPhone");
							basewriter.WriteCData(document.getElementById("SERVICE_MAGIC_cellphone").value);
							basewriter.EndNode();  // end contactMethod
						}
						if (document.getElementById("SERVICE_MAGIC_eveningphone").value)
						{
							basewriter.BeginNode("contactMethod");
							basewriter.Attrib("type", "eveningPhone");
							basewriter.WriteCData(document.getElementById("SERVICE_MAGIC_eveningphone").value);
							basewriter.EndNode();  // end contactMethod
						}
						basewriter.BeginNode("contactMethod");
						basewriter.Attrib("type", "email");
						basewriter.WriteCData(sEmail);
						basewriter.EndNode();  // end contactMethod
				    
						basewriter.EndNode();  // end contactMethods
						basewriter.EndNode();  // end contact
				    
						basewriter.BeginNode("location"); 
						//add sub-elements
						basewriter.BeginNode("addressLine1");
						basewriter.WriteCData(sAddress+sAddress2);
						basewriter.EndNode();  // end addressLine1
						basewriter.BeginNode("city");
						basewriter.WriteCData(sCity);
						basewriter.EndNode();  // end city
						basewriter.BeginNode("zip");
						basewriter.WriteCData(sZip);
						basewriter.EndNode();  // end zip
				    
						basewriter.EndNode();  // end location
						basewriter.EndNode();  // end customer
	
						basewriter.BeginNode("task");
						//Add an attribute to the previously created element 
						basewriter.Attrib("description", "Basement Remodel");
						basewriter.Attrib("oid", "40128");
				    
						basewriter.BeginNode("interview");
				    
						basewriter.BeginNode("question");
						basewriter.Attrib("id", "4411");
						basewriter.Attrib("description", "Design Preparation");
						basewriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						arbaseStage='';
						for(a=0;a<4;a++)
						{
								teststring = "SERVICE_MAGIC_base_design_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arbaseStage =document.getElementById(teststring).value.split('|'); 
								}
						}									    
						basewriter.BeginNode("answer");
						basewriter.Attrib("id", arbaseStage[0]);
						basewriter.Attrib("questionID", "4411");
						basewriter.WriteCData(arbaseStage[1]);
						basewriter.EndNode();  // end answer
						basewriter.EndNode();  // end question
				    
						basewriter.BeginNode("question");
						basewriter.Attrib("id", "5422");
						basewriter.Attrib("description", "Square Footage");
						basewriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						baseDesign = document.getElementById("SERVICE_MAGIC_base_square_footage").value;
						//string[] arbaseDesign = new string[1];
						//arbaseDesign = baseDesign.Split(splitter);
						basewriter.BeginNode("answer");
						basewriter.Attrib("id", "0");
						basewriter.Attrib("questionID", "5422");
						basewriter.WriteCData(baseDesign);
						basewriter.EndNode();  // end answer
						basewriter.EndNode();  // end question
				    
					
					
					    baseRemodel = 0;
						for(a=0;a<9;a++)
						{
								teststring = "SERVICE_MAGIC_base_remodel_"+a;
								baseRemodel |=document.getElementById(teststring).checked; 
						}	
										
						if (baseRemodel != 0)
						{
							basewriter.BeginNode("question");
							basewriter.Attrib("id", "5389");
							basewriter.Attrib("description", "Intention of Remodel");
							basewriter.Attrib("type", "TASK_INTERVIEW"); 			    
							for(a=0;a<9;a++)
							{
								teststring = "SERVICE_MAGIC_base_remodel_"+a;
								arbaseRemodel =document.getElementById(teststring).value.split('|');

								basewriter.BeginNode("answer");
								basewriter.Attrib("id", arbaseRemodel[0]);
								basewriter.Attrib("questionID", "5389");
								basewriter.WriteCData(arbaseRemodel[1]);
								basewriter.EndNode();  // end answer 
							}
							basewriter.EndNode();  // end question
						}
				    
						basewriter.BeginNode("question");
						basewriter.Attrib("id", "90000");
						basewriter.Attrib("description", "Request Stage");
						basewriter.Attrib("type", "TASK_INTERVIEW"); 		

						arbaseStatus = document.getElementById("SERVICE_MAGIC_base_status").value.split('|');
						basewriter.BeginNode("answer");
						basewriter.Attrib("id", arbaseStatus[0]);
						basewriter.Attrib("questionID", "90000");
						basewriter.WriteCData(arbaseStatus[1]);
						basewriter.EndNode();  // end answer
						basewriter.EndNode();  // end question
				    
					    baseFeat = 0;
						for(a=0;a<9;a++)
						{
								teststring = "SERVICE_MAGIC_base_features_"+a;
								baseFeat |=document.getElementById(teststring).checked; 
						}
											

						if (baseFeat != 0)
						{
							basewriter.BeginNode("question");
							basewriter.Attrib("id", "5390");
							basewriter.Attrib("description", "Features of Remodel");
							basewriter.Attrib("type", "TASK_INTERVIEW"); 			    
							for(a=0;a<9;a++)
							{
								teststring = "SERVICE_MAGIC_base_features_"+a;
								arbaseFeat =document.getElementById(teststring).value.split('|');
								
								basewriter.BeginNode("answer");
								basewriter.Attrib("id", arbaseFeat[0]);
								basewriter.Attrib("questionID", "5390");
								basewriter.WriteCData(arbaseFeat[1]);
								basewriter.EndNode();  // end answer 
							}
							basewriter.EndNode();  // end question
						}
				    
						basewriter.BeginNode("question");
						basewriter.Attrib("id", "80000");
						basewriter.Attrib("description", "Desired Completion Date");
						basewriter.Attrib("type", "TASK_INTERVIEW"); 		


						arbaseComp = document.getElementById("SERVICE_MAGIC_base_completed").value.split('|');
						basewriter.BeginNode("answer");
						basewriter.Attrib("id", arbaseComp[0]);
						basewriter.Attrib("questionID", "80000");
						basewriter.WriteCData(arbaseComp[1]);
						basewriter.EndNode();  // end answer
						basewriter.EndNode();  // end question
				    
						basewriter.BeginNode("question");
						basewriter.Attrib("id", "5627");
						basewriter.Attrib("description", "Basement Access");
						basewriter.Attrib("type", "TASK_INTERVIEW"); 
						
						
						arbaseExt='';
						for(a=0;a<4;a++)
						{
								teststring = "SERVICE_MAGIC_base_access_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arbaseExt =document.getElementById(teststring).value.split('|'); 
								}
						}										    
						basewriter.BeginNode("answer");
						basewriter.Attrib("id", arbaseExt[0]);
						basewriter.Attrib("questionID", "5627");
						basewriter.WriteCData(arbaseExt[1]);
						basewriter.EndNode();  // end answer
						basewriter.EndNode();  // end question
				    
						basewriter.BeginNode("question");
						basewriter.Attrib("id", "70000");
						basewriter.Attrib("description", "Financing Requested");
						basewriter.Attrib("type", "TASK_INTERVIEW"); 
						
						arbaseFin = document.getElementById("SERVICE_MAGIC_base_financing").value.split('|');
						basewriter.BeginNode("answer");
						basewriter.Attrib("id", arbaseFin[0]);
						basewriter.Attrib("questionID", "70000");
						basewriter.WriteCData(arbaseFin[1]);
						basewriter.EndNode();  // end answer
						basewriter.EndNode();  // end question
				    
						basewriter.BeginNode("question");
						basewriter.Attrib("id", "5645");
						basewriter.Attrib("description", "Expected Level of Quality");
						basewriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						arbaseSign='';
						for(a=0;a<4;a++)
						{
								teststring = "SERVICE_MAGIC_base_craftsmanship_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arbaseSign =document.getElementById(teststring).value.split('|'); 
								}
						}								    
						basewriter.BeginNode("answer");
						basewriter.Attrib("id", arbaseSign[0]);
						basewriter.Attrib("questionID", "5645");
						basewriter.WriteCData(arbaseSign[1]);
						basewriter.EndNode();  // end answer
						basewriter.EndNode();  // end question
				    
						basewriter.BeginNode("question");
						basewriter.Attrib("id", "30000");
						basewriter.Attrib("description", "Historical Work");
						basewriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var baseHist = "No";
						var baseHistID = "10002";
						
						if (document.getElementById("SERVICE_MAGIC_base_historical").checked)
						{
							baseHist = "Yes";
							baseHistID = "10001";
						}
						//string[] arbaseHist = new string[1];
						//arbaseHist = baseHist.Split(splitter);
						basewriter.BeginNode("answer");
						basewriter.Attrib("id", baseHistID);
						basewriter.Attrib("questionID", "30000");
						basewriter.WriteCData(baseHist);
						basewriter.EndNode();  // end answer
						basewriter.EndNode();  // end question
				    
						basewriter.BeginNode("question");
						basewriter.Attrib("id", "50000");
						basewriter.Attrib("description", "Covered by Insurance");
						basewriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var baseIns = "No";
						var baseInsID = "10002";
						if (document.getElementById("SERVICE_MAGIC_base_insurance").checked)
						{
							baseIns = "Yes";
							baseInsID = "10001";
						}
						//string[] arbaseIns = new string[1];
						//arbaseIns = baseIns.Split(splitter);
						basewriter.BeginNode("answer");
						basewriter.Attrib("id", baseInsID);
						basewriter.Attrib("questionID", "50000");
						basewriter.WriteCData(baseIns);
						basewriter.EndNode();  // end answer
						basewriter.EndNode();  // end question
				    
						basewriter.BeginNode("question");
						basewriter.Attrib("id", "10019");
						basewriter.Attrib("description", "Request for Commercial Location");
						basewriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var baseComm = "No";
						var baseCommID = "10002";						
						if (document.getElementById("SERVICE_MAGIC_base_commercial").checked)
						{
							baseComm = "Yes";
							baseCommID = "10001";
						}
						//string[] arbaseComm = new string[1];
						//arbaseComm = baseComm.Split(splitter);
						basewriter.BeginNode("answer");
						basewriter.Attrib("id", baseCommID);
						basewriter.Attrib("questionID", "10019");
						basewriter.WriteCData(baseComm);
						basewriter.EndNode();  // end answer
						basewriter.EndNode();  // end question
				    
						basewriter.BeginNode("question");
						basewriter.Attrib("id", "1700");
						basewriter.Attrib("description", "Consumer Owns Home");
						basewriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var baseOwn = "No";
						var baseOwnID = "10002";
						if (document.getElementById("SERVICE_MAGIC_base_own_home").checked)
						{
							baseOwn = "Yes";
							baseOwnID = "10001";
						}
						//string[] arbaseComm = new string[1];
						//arbaseComm = baseComm.Split(splitter);
						basewriter.BeginNode("answer");
						basewriter.Attrib("id", baseOwnID);
						basewriter.Attrib("questionID", "1700");
						basewriter.WriteCData(baseOwn);
						basewriter.EndNode();  // end answer
						basewriter.EndNode();  // end question
				    
						//End the interview element
						basewriter.EndNode();  // end interview
				    
						//End the task element
						basewriter.EndNode();  // end task
				    
						// end the root element
						basewriter.EndNode();
				    
						//basewriter.Flush();         
						//Write the XML to file and close the writer
						basewriter.Close();  
						document.getElementById("SERVICE_MAGIC_basement_XML").value=(baseFile+basewriter.ToString().replace(/</g,"\n<"));		
						
}//endif
if(document.getElementById("Service_Magic_siding").checked){						
///////////////////////for vinyl siding install or replace///////////////////////////////////////////////////////////////
						var sideFile;
						var sidewriter = new XMLWriter() ;
						sideFile = "<?xml version='1.0' encoding='UTF-8'?>";
						sideFile += "<!DOCTYPE serviceRequest SYSTEM 'http://www.servicemagic.com/dtd/submitServiceRequest.dtd'>";
						
						
						//Write the root element
						sidewriter.BeginNode("serviceRequest");
				    
						//Add an attribute to the previously created element 
						sidewriter.Attrib("affiliateCode", "silvercarrot3");
						sidewriter.Attrib("version", "1.0");
						sidewriter.Attrib("testOnly", "false");
				    
						//Start an element
						sidewriter.BeginNode("customer");
						sidewriter.BeginNode("contact");
				    
						sidewriter.BeginNode("firstName");
						sidewriter.WriteCData(sFirst);
						sidewriter.EndNode();  // end firstName
						sidewriter.BeginNode("lastName");
						sidewriter.WriteCData(sLast);
						sidewriter.EndNode();  // end lastName
						arsideContact = document.getElementById("SERVICE_MAGIC_preferredContactTime").value.split('|');
						sidewriter.BeginNode("contactTime");
						sidewriter.WriteCData(arsideContact[1]);
						sidewriter.EndNode();  // end contactTime
				    
						sidewriter.BeginNode("contactMethods");
				    
						sidewriter.BeginNode("contactMethod");
						sidewriter.Attrib("type", "dayPhone");
						sidewriter.WriteCData(sPhone);
						sidewriter.EndNode();  // end contactMethod
						if (document.getElementById("SERVICE_MAGIC_cellphone").value)
						{
							sidewriter.BeginNode("contactMethod");
							sidewriter.Attrib("type", "cellPhone");
							sidewriter.WriteCData(document.getElementById("SERVICE_MAGIC_cellphone").value);
							sidewriter.EndNode();  // end contactMethod
						}
						if (document.getElementById("SERVICE_MAGIC_eveningphone").value)
						{
							sidewriter.BeginNode("contactMethod");
							sidewriter.Attrib("type", "eveningPhone");
							sidewriter.WriteCData(document.getElementById("SERVICE_MAGIC_eveningphone").value);
							sidewriter.EndNode();  // end contactMethod
						}
						sidewriter.BeginNode("contactMethod");
						sidewriter.Attrib("type", "email");
						sidewriter.WriteCData(sEmail);
						sidewriter.EndNode();  // end contactMethod
				    
						sidewriter.EndNode();  // end contactMethods
						sidewriter.EndNode();  // end contact
				    
						sidewriter.BeginNode("location"); 
						//add sub-elements
						sidewriter.BeginNode("addressLine1");
						sidewriter.WriteCData(sAddress+sAddress2);
						sidewriter.EndNode();  // end addressLine1
						sidewriter.BeginNode("city");
						sidewriter.WriteCData(sCity);
						sidewriter.EndNode();  // end city
						sidewriter.BeginNode("zip");
						sidewriter.WriteCData(sZip);
						sidewriter.EndNode();  // end zip
				    
						sidewriter.EndNode();  // end location
						sidewriter.EndNode();  // end customer
	
						sidewriter.BeginNode("task");
						//Add an attribute to the previously created element 
						sidewriter.Attrib("description", "Vinyl Siding - Install or Replace");
						sidewriter.Attrib("oid", "40146");
				    
						sidewriter.BeginNode("interview");
				    
						sidewriter.BeginNode("question");
						sidewriter.Attrib("id", "1056");
						sidewriter.Attrib("description", "Project Type");

						sidewriter.Attrib("type", "TASK_INTERVIEW"); 		
						arsideStage='';
						for(a=0;a<4;a++)
						{
								teststring = "SERVICE_MAGIC_side_project_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arsideStage =document.getElementById(teststring).value.split('|'); 
								}
						}								    
						
						sidewriter.BeginNode("answer");
						sidewriter.Attrib("id", arsideStage[0]);
						sidewriter.Attrib("questionID", "1056");
						sidewriter.WriteCData(arsideStage[1]);
						sidewriter.EndNode();  // end answer
						sidewriter.EndNode();  // end question
				    
						sidewriter.BeginNode("question");
						sidewriter.Attrib("id", "4449");
						sidewriter.Attrib("description", "Age of Home");
						sidewriter.Attrib("type", "TASK_INTERVIEW"); 	

						sideFeat = document.getElementById("SERVICE_MAGIC_side_years_old").value;
						sidewriter.BeginNode("answer");
						sidewriter.Attrib("id", "0");
						sidewriter.Attrib("questionID", "4449");
						sidewriter.WriteCData(sideFeat);
						sidewriter.EndNode();  // end answer
						sidewriter.EndNode();  // end question				    
						sidewriter.BeginNode("question");
						sidewriter.Attrib("id", "5498");
						sidewriter.Attrib("description", "Type of Siding");
						sidewriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						arsideDesign='';
						for(a=0;a<4;a++)
						{
								teststring = "SERVICE_MAGIC_side_kind_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arsideDesign =document.getElementById(teststring).value.split('|'); 
								}
						}			    
						sidewriter.BeginNode("answer");
						sidewriter.Attrib("id", arsideDesign[0]);
						sidewriter.Attrib("questionID", "5498");
						sidewriter.WriteCData(arsideDesign[1]);
						sidewriter.EndNode();  // end answer
						sidewriter.EndNode();  // end question
				    
						sidewriter.BeginNode("question");
						sidewriter.Attrib("id", "90000");
						sidewriter.Attrib("description", "Request Stage");
						sidewriter.Attrib("type", "TASK_INTERVIEW"); 
						
						arsideStatus = document.getElementById("SERVICE_MAGIC_side_status").value.split('|');									    
						sidewriter.BeginNode("answer");
						sidewriter.Attrib("id", arsideStatus[0]);
						sidewriter.Attrib("questionID", "90000");
						sidewriter.WriteCData(arsideStatus[1]);
						sidewriter.EndNode();  // end answer
						sidewriter.EndNode();  // end question
				    
						sidewriter.BeginNode("question");
						sidewriter.Attrib("id", "5287");
						sidewriter.Attrib("description", "Square Footage");
						sidewriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						sideExt = document.getElementById("SERVICE_MAGIC_side_square_footage").value;
						sidewriter.BeginNode("answer");
						sidewriter.Attrib("id", "0");
						sidewriter.Attrib("questionID", "5287");
						sidewriter.WriteCData(sideExt);
						sidewriter.EndNode();  // end answer
						sidewriter.EndNode();  // end question
				    
						sidewriter.BeginNode("question");
						sidewriter.Attrib("id", "80000");
						sidewriter.Attrib("description", "Desired Completion Date");
						sidewriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						arsideComp = document.getElementById("SERVICE_MAGIC_side_completed").value.split('|');									    
						sidewriter.BeginNode("answer");
						sidewriter.Attrib("id", arsideComp[0]);
						sidewriter.Attrib("questionID", "80000");
						sidewriter.WriteCData(arsideComp[1]);
						sidewriter.EndNode();  // end answer
						sidewriter.EndNode();  // end question
				    
						sidewriter.BeginNode("question");
						sidewriter.Attrib("id", "1059");
						sidewriter.Attrib("description", "Stories of Home");
						sidewriter.Attrib("type", "TASK_INTERVIEW"); 		

						arsideRemodel='';
						for(a=0;a<3;a++)
						{
								teststring = "SERVICE_MAGIC_side_stories_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arsideRemodel =document.getElementById(teststring).value.split('|'); 
								}
						}					
						sidewriter.BeginNode("answer");
						sidewriter.Attrib("id", arsideRemodel[0]);
						sidewriter.Attrib("questionID", "1059");
						sidewriter.WriteCData(arsideRemodel[1]);
						sidewriter.EndNode();  // end answer
						sidewriter.EndNode();  // end question
				    
						sidewriter.BeginNode("question");
						sidewriter.Attrib("id", "70000");
						sidewriter.Attrib("description", "Financing Requested");
						sidewriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						arsideFin = document.getElementById("SERVICE_MAGIC_side_financing").value.split('|');
						sidewriter.BeginNode("answer");
						sidewriter.Attrib("id", arsideFin[0]);
						sidewriter.Attrib("questionID", "70000");
						sidewriter.WriteCData(arsideFin[1]);
						sidewriter.EndNode();  // end answer
						sidewriter.EndNode();  // end question

					    sideSign = 0;
						for(a=0;a<6;a++)
						{
								teststring = "SERVICE_MAGIC_side_additions_"+a;
								sideSign |=document.getElementById(teststring).checked; 
						}
						if (sideSign != 0)
						{
							sidewriter.BeginNode("question");
							sidewriter.Attrib("id", "5288");
							sidewriter.Attrib("description", "Additional Items");
							sidewriter.Attrib("type", "TASK_INTERVIEW"); 
							for(a=0;a<6;a++)
							{
								teststring = "SERVICE_MAGIC_side_additions_"+a;
								arsideSign =document.getElementById(teststring).value.split('|');
								
								sidewriter.BeginNode("answer");
								sidewriter.Attrib("id", arsideSign[0]);
								sidewriter.Attrib("questionID", "5288");
								sidewriter.WriteCData(arsideSign[1]);
								sidewriter.EndNode();  // end answer 
							}
							sidewriter.EndNode();  // end question
						}
				    
						sidewriter.BeginNode("question");
						sidewriter.Attrib("id", "10019");
						sidewriter.Attrib("description", "Request for Commercial Location");
						sidewriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var sideComm = "No";
						var sideCommID = "10002";					
						if (document.getElementById("SERVICE_MAGIC_side_commercial").checked)
						{
							sideComm = "Yes";
							sideCommID = "10001";
						}
						sidewriter.BeginNode("answer");
						sidewriter.Attrib("id", sideCommID);
						sidewriter.Attrib("questionID", "10019");
						sidewriter.WriteCData(sideComm);
						sidewriter.EndNode();  // end answer
						sidewriter.EndNode();  // end question
				    
						sidewriter.BeginNode("question");
						sidewriter.Attrib("id", "1700");
						sidewriter.Attrib("description", "Consumer Owns Home");
						sidewriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var sideOwn = "No";
						var sideOwnID = "10002";
						if (document.getElementById("SERVICE_MAGIC_side_own_home").checked)
						{
							sideOwn = "Yes";
							sideOwnID = "10001";
						}
						//string[] arsideComm = new string[1];
						//arsideComm = sideComm.Split(splitter);
						sidewriter.BeginNode("answer");
						sidewriter.Attrib("id", sideOwnID);
						sidewriter.Attrib("questionID", "1700");
						sidewriter.WriteCData(sideOwn);
						sidewriter.EndNode();  // end answer
						sidewriter.EndNode();  // end question
				    
						//End the interview element
						sidewriter.EndNode();  // end interview
				    
						//End the task element
						sidewriter.EndNode();  // end task
				    
						// end the root element
						sidewriter.EndNode();
				    
						//sidewriter.Flush();         
						//Write the XML to file and close the writer
						sidewriter.Close();  
 						document.getElementById("SERVICE_MAGIC_siding_XML").value=(sideFile+sidewriter.ToString().replace(/</g,"\n<"));				
						
}//endif
if(document.getElementById("Service_Magic_kitchen").checked){						

///////////////////////for kitchen remodel///////////////////////////////////////////////////////////////
						var kitchFile;
						var kitchwriter = new XMLWriter() ;
						kitchFile = "<?xml version='1.0' encoding='UTF-8'?>";
						kitchFile += "<!DOCTYPE serviceRequest SYSTEM 'http://www.servicemagic.com/dtd/submitServiceRequest.dtd'>";
				        
						//Write the root element
						kitchwriter.BeginNode("serviceRequest");
				    
						//Add an attribute to the previously created element 
						kitchwriter.Attrib("affiliateCode", "silvercarrot3");
						kitchwriter.Attrib("version", "1.0");
						kitchwriter.Attrib("testOnly", "false");
				    
						//Start an element
						kitchwriter.BeginNode("customer");
						kitchwriter.BeginNode("contact");
				    
						kitchwriter.BeginNode("firstName");
						kitchwriter.WriteCData(sFirst);
						kitchwriter.EndNode();  // end firstName
						kitchwriter.BeginNode("lastName");
						kitchwriter.WriteCData(sLast);
						kitchwriter.EndNode();  // end lastName
						
						arkitchContact = document.getElementById("SERVICE_MAGIC_preferredContactTime").value.split('|');
						kitchwriter.BeginNode("contactTime");
						kitchwriter.WriteCData(arkitchContact[1]);
						kitchwriter.EndNode();  // end contactTime
				    
						kitchwriter.BeginNode("contactMethods");
				    
						kitchwriter.BeginNode("contactMethod");
						kitchwriter.Attrib("type", "dayPhone");
						kitchwriter.WriteCData(sPhone);
						kitchwriter.EndNode();  // end contactMethod
						if (document.getElementById("SERVICE_MAGIC_cellphone").value)
						{
							kitchwriter.BeginNode("contactMethod");
							kitchwriter.Attrib("type", "cellPhone");
							kitchwriter.WriteCData(document.getElementById("SERVICE_MAGIC_cellphone").value);
							kitchwriter.EndNode();  // end contactMethod
						}
						if (document.getElementById("SERVICE_MAGIC_eveningphone").value)
						{
							kitchwriter.BeginNode("contactMethod");
							kitchwriter.Attrib("type", "eveningPhone");
							kitchwriter.WriteCData(document.getElementById("SERVICE_MAGIC_eveningphone").value);
							kitchwriter.EndNode();  // end contactMethod
						}
						kitchwriter.BeginNode("contactMethod");
						kitchwriter.Attrib("type", "email");
						kitchwriter.WriteCData(sEmail);
						kitchwriter.EndNode();  // end contactMethod
				    
						kitchwriter.EndNode();  // end contactMethods
						kitchwriter.EndNode();  // end contact
				    
						kitchwriter.BeginNode("location"); 
						//add sub-elements
						kitchwriter.BeginNode("addressLine1");
						kitchwriter.WriteCData(sAddress+sAddress2);
						kitchwriter.EndNode();  // end addressLine1
						kitchwriter.BeginNode("city");
						kitchwriter.WriteCData(sCity);
						kitchwriter.EndNode();  // end city
						kitchwriter.BeginNode("zip");
						kitchwriter.WriteCData(sZip);
						kitchwriter.EndNode();  // end zip
				    
						kitchwriter.EndNode();  // end location
						kitchwriter.EndNode();  // end customer
	
						kitchwriter.BeginNode("task");
						//Add an attribute to the previously created element 
						kitchwriter.Attrib("description", "Kitchen Remodel");
						kitchwriter.Attrib("oid", "40131");
				    
						kitchwriter.BeginNode("interview");
				    
						kitchwriter.BeginNode("question");
						kitchwriter.Attrib("id", "4411");
						kitchwriter.Attrib("description", "Design Preparation");
						kitchwriter.Attrib("type", "TASK_INTERVIEW"); 		


						arkitchStage='';
						for(a=0;a<4;a++)
						{
								teststring = "SERVICE_MAGIC_kitch_design_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arkitchStage =document.getElementById(teststring).value.split('|'); 
								}
						}								    
						kitchwriter.BeginNode("answer");
						kitchwriter.Attrib("id", arkitchStage[0]);
						kitchwriter.Attrib("questionID", "4411");
						kitchwriter.WriteCData(arkitchStage[1]);
						kitchwriter.EndNode();  // end answer
						kitchwriter.EndNode();  // end question
				    
						kitchwriter.BeginNode("question");
						kitchwriter.Attrib("id", "5423");
						kitchwriter.Attrib("description", "Square Footage");
						kitchwriter.Attrib("type", "TASK_INTERVIEW"); 		
							 
						kitchDesign = document.getElementById("SERVICE_MAGIC_kitch_square_footage").value;
						kitchwriter.BeginNode("answer");
						kitchwriter.Attrib("id", "0");
						kitchwriter.Attrib("questionID", "5423");
						kitchwriter.WriteCData(kitchDesign);
						kitchwriter.EndNode();  // end answer
						kitchwriter.EndNode();  // end question
				    
					
					
					    kitchRemodel = 0;
						for(a=0;a<8;a++)
						{
								teststring = "SERVICE_MAGIC_kitch_remodel_"+a;
								kitchRemodel |=document.getElementById(teststring).checked; 
						}					
						if (kitchRemodel != 0)
						{
							kitchwriter.BeginNode("question");
							kitchwriter.Attrib("id", "5391");
							kitchwriter.Attrib("description", "Extensiveness of Remodel");
							kitchwriter.Attrib("type", "TASK_INTERVIEW"); 			    
							for(a=0;a<8;a++)
							{
								teststring = "SERVICE_MAGIC_kitch_remodel_"+a;
								arkitchRemodel =document.getElementById(teststring).value.split('|');

								kitchwriter.BeginNode("answer");
								kitchwriter.Attrib("id", arkitchRemodel[0]);
								kitchwriter.Attrib("questionID", "5391");
								kitchwriter.WriteCData(arkitchRemodel[1]);
								kitchwriter.EndNode();  // end answer 
							}
							kitchwriter.EndNode();  // end question
						}
				    
						kitchwriter.BeginNode("question");
						kitchwriter.Attrib("id", "90000");
						kitchwriter.Attrib("description", "Request Stage");
						kitchwriter.Attrib("type", "TASK_INTERVIEW"); 
								
						arkitchStatus = document.getElementById("SERVICE_MAGIC_kitch_status").value.split('|');
						kitchwriter.BeginNode("answer");
						kitchwriter.Attrib("id", arkitchStatus[0]);
						kitchwriter.Attrib("questionID", "90000");
						kitchwriter.WriteCData(arkitchStatus[1]);
						kitchwriter.EndNode();  // end answer
						kitchwriter.EndNode();  // end question


					    kitchFeat = 0;
						for(a=0;a<12;a++)
						{
								teststring = "SERVICE_MAGIC_kitch_features_"+a;
								kitchFeat |=document.getElementById(teststring).checked; 
						}

						if (kitchFeat != 0)
						{
							kitchwriter.BeginNode("question");
							kitchwriter.Attrib("id", "5392");
							kitchwriter.Attrib("description", "Features to be Remodeled");
							kitchwriter.Attrib("type", "TASK_INTERVIEW"); 			    
							for(a=0;a<12;a++)
							{
								teststring = "SERVICE_MAGIC_kitch_features_"+a;
								arkitchFeat =document.getElementById(teststring).value.split('|');
								
								kitchwriter.BeginNode("answer");
								kitchwriter.Attrib("id", arkitchFeat[0]);
								kitchwriter.Attrib("questionID", "5392");
								kitchwriter.WriteCData(arkitchFeat[1]);
								kitchwriter.EndNode();  // end answer 
							}
							kitchwriter.EndNode();  // end question
						}
				    
						kitchwriter.BeginNode("question");
						kitchwriter.Attrib("id", "80000");
						kitchwriter.Attrib("description", "Desired Completion Date");
						kitchwriter.Attrib("type", "TASK_INTERVIEW"); 
						
						arkitchComp = document.getElementById("SERVICE_MAGIC_kitch_completed").value.split('|');
						kitchwriter.BeginNode("answer");
						kitchwriter.Attrib("id", arkitchComp[0]);
						kitchwriter.Attrib("questionID", "80000");
						kitchwriter.WriteCData(arkitchComp[1]);
						kitchwriter.EndNode();  // end answer
						kitchwriter.EndNode();  // end question


					    kitchExt = 0;
						for(a=0;a<6;a++)
						{
								teststring = "SERVICE_MAGIC_kitch_elements_"+a;
								kitchExt |=document.getElementById(teststring).checked; 
						}
						if (kitchExt != 0)
						{
							kitchwriter.BeginNode("question");
							kitchwriter.Attrib("id", "5393");
							kitchwriter.Attrib("description", "Special Elements");
							kitchwriter.Attrib("type", "TASK_INTERVIEW"); 			    
							for(a=0;a<6;a++)
							{
								teststring = "SERVICE_MAGIC_kitch_elements_"+a;
								arkitchExt =document.getElementById(teststring).value.split('|');

								kitchwriter.BeginNode("answer");
								kitchwriter.Attrib("id", arkitchExt[0]);
								kitchwriter.Attrib("questionID", "5393");
								kitchwriter.WriteCData(arkitchExt[1]);
								kitchwriter.EndNode();  // end answer 
							}
							kitchwriter.EndNode();  // end question
						}
				    
						kitchwriter.BeginNode("question");
						kitchwriter.Attrib("id", "70000");
						kitchwriter.Attrib("description", "Financing Requested");
						kitchwriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						arkitchFin = document.getElementById("SERVICE_MAGIC_kitch_financing").value.split('|');
						kitchwriter.BeginNode("answer");
						kitchwriter.Attrib("id", arkitchFin[0]);
						kitchwriter.Attrib("questionID", "70000");
						kitchwriter.WriteCData(arkitchFin[1]);
						kitchwriter.EndNode();  // end answer
						kitchwriter.EndNode();  // end question
				    
						kitchwriter.BeginNode("question");
						kitchwriter.Attrib("id", "5645");
						kitchwriter.Attrib("description", "Expected Level of Quality");
						kitchwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						arkitchSign='';
						for(a=0;a<4;a++)
						{
								teststring = "SERVICE_MAGIC_kitch_craftsmanship_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arkitchSign =document.getElementById(teststring).value.split('|'); 
								}
						}	

						kitchwriter.BeginNode("answer");
						kitchwriter.Attrib("id", arkitchSign[0]);
						kitchwriter.Attrib("questionID", "5645");
						kitchwriter.WriteCData(arkitchSign[1]);
						kitchwriter.EndNode();  // end answer
						kitchwriter.EndNode();  // end question
				    
						kitchwriter.BeginNode("question");
						kitchwriter.Attrib("id", "30000");
						kitchwriter.Attrib("description", "Historical Work");
						kitchwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var kitchHist = "No";
						var kitchHistID = "10002";
						if (document.getElementById("SERVICE_MAGIC_kitch_historical").checked)
						{
							kitchHist = "Yes";
							kitchHistID = "10001";
						}
						//string[] arkitchHist = new string[1];
						//arkitchHist = kitchHist.Split(splitter);
						kitchwriter.BeginNode("answer");
						kitchwriter.Attrib("id", kitchHistID);
						kitchwriter.Attrib("questionID", "30000");
						kitchwriter.WriteCData(kitchHist);
						kitchwriter.EndNode();  // end answer
						kitchwriter.EndNode();  // end question
				    
						kitchwriter.BeginNode("question");
						kitchwriter.Attrib("id", "50000");
						kitchwriter.Attrib("description", "Covered by Insurance");
						kitchwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var kitchIns = "No";
						var kitchInsID = "10002";
						if (document.getElementById("SERVICE_MAGIC_kitch_insurance").checked)
						{
							kitchIns = "Yes";
							kitchInsID = "10001";
						}
						//string[] arkitchIns = new string[1];
						//arkitchIns = kitchIns.Split(splitter);
						kitchwriter.BeginNode("answer");
						kitchwriter.Attrib("id", kitchInsID);
						kitchwriter.Attrib("questionID", "50000");
						kitchwriter.WriteCData(kitchIns);
						kitchwriter.EndNode();  // end answer
						kitchwriter.EndNode();  // end question
				    
						kitchwriter.BeginNode("question");
						kitchwriter.Attrib("id", "10019");
						kitchwriter.Attrib("description", "Request for Commercial Location");
						kitchwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var kitchComm = "No";
						var kitchCommID = "10002";
						if (document.getElementById("SERVICE_MAGIC_kitch_commercial").checked)
						{
							kitchComm = "Yes";
							kitchCommID = "10001";
						}
						//string[] arkitchComm = new string[1];
						//arkitchComm = kitchComm.Split(splitter);
						kitchwriter.BeginNode("answer");
						kitchwriter.Attrib("id", kitchCommID);
						kitchwriter.Attrib("questionID", "10019");
						kitchwriter.WriteCData(kitchComm);
						kitchwriter.EndNode();  // end answer
						kitchwriter.EndNode();  // end question
				    
						kitchwriter.BeginNode("question");
						kitchwriter.Attrib("id", "1700");
						kitchwriter.Attrib("description", "Consumer Owns Home");
						kitchwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var kitchOwn = "No";
						var kitchOwnID = "10002";
						if (document.getElementById("SERVICE_MAGIC_kitch_own_home").checked)
						{
							kitchOwn = "Yes";
							kitchOwnID = "10001";
						}
						//string[] arkitchComm = new string[1];
						//arkitchComm = kitchComm.Split(splitter);
						kitchwriter.BeginNode("answer");
						kitchwriter.Attrib("id", kitchOwnID);
						kitchwriter.Attrib("questionID", "1700");
						kitchwriter.WriteCData(kitchOwn);
						kitchwriter.EndNode();  // end answer
						kitchwriter.EndNode();  // end question
				    
						//End the interview element
						kitchwriter.EndNode();  // end interview
				    
						//End the task element
						kitchwriter.EndNode();  // end task
				    
						// end the root element
						kitchwriter.EndNode();
				    
						//kitchwriter.Flush();         
						//Write the XML to file and close the writer
						kitchwriter.Close();  
						document.getElementById("SERVICE_MAGIC_kitchen_XML").value=(kitchFile+kitchwriter.ToString().replace(/</g,"\n<"));				
						
						
}//endif
if(document.getElementById("Service_Magic_windows").checked){						

///////////////////////for install Multiple windows///////////////////////////////////////////////////////////////
						var windowFile;
						var windowwriter = new XMLWriter() ;
						windowFile = "<?xml version='1.0' encoding='UTF-8'?>";
						windowFile += "<!DOCTYPE serviceRequest SYSTEM 'http://www.servicemagic.com/dtd/submitServiceRequest.dtd'>";
						//Write the root element
						windowwriter.BeginNode("serviceRequest");
				    
						//Add an attribute to the previously created element 
						windowwriter.Attrib("affiliateCode", "silvercarrot3");
						windowwriter.Attrib("version", "1.0");
						windowwriter.Attrib("testOnly", "false");
				    
						//Start an element
						windowwriter.BeginNode("customer");
						windowwriter.BeginNode("contact");
				    
						windowwriter.BeginNode("firstName");
						windowwriter.WriteCData(sFirst);
						windowwriter.EndNode();  // end firstName
						windowwriter.BeginNode("lastName");
						windowwriter.WriteCData(sLast);
						windowwriter.EndNode();  // end lastName
						arwindowContact = document.getElementById("SERVICE_MAGIC_preferredContactTime").value.split('|');
						windowwriter.BeginNode("contactTime");
						windowwriter.WriteCData(arwindowContact[1]);
						windowwriter.EndNode();  // end contactTime
				    
						windowwriter.BeginNode("contactMethods");
				    
						windowwriter.BeginNode("contactMethod");
						windowwriter.Attrib("type", "dayPhone");
						windowwriter.WriteCData(sPhone);
						windowwriter.EndNode();  // end contactMethod
						if (document.getElementById("SERVICE_MAGIC_cellphone").value)
						{
							windowwriter.BeginNode("contactMethod");
							windowwriter.Attrib("type", "cellPhone");
							windowwriter.WriteCData(document.getElementById("SERVICE_MAGIC_cellphone").value);
							windowwriter.EndNode();  // end contactMethod
						}
						if (document.getElementById("SERVICE_MAGIC_eveningphone").value)
						{
							windowwriter.BeginNode("contactMethod");
							windowwriter.Attrib("type", "eveningPhone");
							windowwriter.WriteCData(document.getElementById("SERVICE_MAGIC_eveningphone").value);
							windowwriter.EndNode();  // end contactMethod
						}
						windowwriter.BeginNode("contactMethod");
						windowwriter.Attrib("type", "email");
						windowwriter.WriteCData(sEmail);
						windowwriter.EndNode();  // end contactMethod
				    
						windowwriter.EndNode();  // end contactMethods
						windowwriter.EndNode();  // end contact
				    
						windowwriter.BeginNode("location"); 
						//add sub-elements
						windowwriter.BeginNode("addressLine1");
						windowwriter.WriteCData(sAddress+sAddress2);
						windowwriter.EndNode();  // end addressLine1
						windowwriter.BeginNode("city");
						windowwriter.WriteCData(sCity);
						windowwriter.EndNode();  // end city
						windowwriter.BeginNode("zip");
						windowwriter.WriteCData(sZip);
						windowwriter.EndNode();  // end zip
				    
						windowwriter.EndNode();  // end location
						windowwriter.EndNode();  // end customer
	
						windowwriter.BeginNode("task");
						//Add an attribute to the previously created element 
						windowwriter.Attrib("description", "Install Multiple Windows");
						windowwriter.Attrib("oid", "40164");
				    
						windowwriter.BeginNode("interview");
				    
						windowwriter.BeginNode("question");
						windowwriter.Attrib("id", "90000");
						windowwriter.Attrib("description", "Request Stage");
						windowwriter.Attrib("type", "TASK_INTERVIEW"); 	
							
						arwindowStatus = document.getElementById("SERVICE_MAGIC_window_status").value.split('|');
						windowwriter.BeginNode("answer");
						windowwriter.Attrib("id", arwindowStatus[0]);
						windowwriter.Attrib("questionID", "90000");
						windowwriter.WriteCData(arwindowStatus[1]);
						windowwriter.EndNode();  // end answer
						windowwriter.EndNode();  // end question

						var windowNum1 = document.getElementById("SERVICE_MAGIC_window_num7222").value;
						var windowNum2 = document.getElementById("SERVICE_MAGIC_window_num7263").value;
						var windowNum3 = document.getElementById("SERVICE_MAGIC_window_num7264").value;
						var windowNum4 = document.getElementById("SERVICE_MAGIC_window_num7265").value;
						var windowNum5 = document.getElementById("SERVICE_MAGIC_window_num7266").value;
						if (windowNum1 != "" || windowNum2 != "" || windowNum3 != "" || windowNum4 != "" || windowNum5 != "")
						{
							windowwriter.BeginNode("question");
							windowwriter.Attrib("id", "160812");
							windowwriter.Attrib("description", "Number of Windows");
							windowwriter.Attrib("type", "TASK_INTERVIEW"); 			    
							if (windowNum1 != "")
							{
								windowwriter.BeginNode("answer");
								windowwriter.Attrib("id", "7222");
								windowwriter.Attrib("questionID", "160812");
								windowwriter.WriteCData(windowNum1 + " Install new windows");
								windowwriter.EndNode();  // end answer 
							}
							if (windowNum2 != "")
							{
								windowwriter.BeginNode("answer");
								windowwriter.Attrib("id", "7263");
								windowwriter.Attrib("questionID", "160812");
								windowwriter.WriteCData(windowNum2 + " Retrofit existing windows with new ones");
								windowwriter.EndNode();  // end answer 
							}
							if (windowNum3 != "")
							{
								windowwriter.BeginNode("answer");
								windowwriter.Attrib("id", "7264");
								windowwriter.Attrib("questionID", "160812");
								windowwriter.WriteCData(windowNum3 + " Replace old windows or patio doors");
								windowwriter.EndNode();  // end answer 
							}
							if (windowNum4 != "")
							{
								windowwriter.BeginNode("answer");
								windowwriter.Attrib("id", "7265");
								windowwriter.Attrib("questionID", "160812");
								windowwriter.WriteCData(windowNum4 + " Cut new windows or patio doors");
								windowwriter.EndNode();  // end answer 
							}
							if (windowNum5 != "")
							{
								windowwriter.BeginNode("answer");
								windowwriter.Attrib("id", "7266");
								windowwriter.Attrib("questionID", "160812");
								windowwriter.WriteCData(windowNum5 + " Install interior windows");
								windowwriter.EndNode();  // end answer 
							}
							windowwriter.EndNode();  // end question
						}
				    
						windowwriter.BeginNode("question");
						windowwriter.Attrib("id", "1382");
						windowwriter.Attrib("description", "Window Frame Material");
						windowwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						arwindowFrame = document.getElementById("SERVICE_MAGIC_window_frame").value.split('|');
						windowwriter.BeginNode("answer");
						windowwriter.Attrib("id", arwindowFrame[0]);
						windowwriter.Attrib("questionID", "1382");
						windowwriter.WriteCData(arwindowFrame[1]);
						windowwriter.EndNode();  // end answer
						windowwriter.EndNode();  // end question
				    
						windowwriter.BeginNode("question");
						windowwriter.Attrib("id", "80000");
						windowwriter.Attrib("description", "Desired Completion Date");
						windowwriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						
						arwindowComp = document.getElementById("SERVICE_MAGIC_window_completed").value.split('|');
						windowwriter.BeginNode("answer");
						windowwriter.Attrib("id", arwindowComp[0]);
						windowwriter.Attrib("questionID", "80000");
						windowwriter.WriteCData(arwindowComp[1]);
						windowwriter.EndNode();  // end answer
						windowwriter.EndNode();  // end question
				    
						windowwriter.BeginNode("question");
						windowwriter.Attrib("id", "1700");
						windowwriter.Attrib("description", "Consumer Owns Home");
						windowwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var windowOwn = "No";
						var windowOwnID = "10002";
						if (document.getElementById("SERVICE_MAGIC_window_own_home").checked)
						{
							windowOwn = "Yes";
							windowOwnID = "10001";
						}
						//string[] arwindowComm = new string[1];
						//arwindowComm = windowComm.Split(splitter);
						windowwriter.BeginNode("answer");
						windowwriter.Attrib("id", windowOwnID);
						windowwriter.Attrib("questionID", "1700");
						windowwriter.WriteCData(windowOwn);
						windowwriter.EndNode();  // end answer
						windowwriter.EndNode();  // end question
				    
						windowwriter.BeginNode("question");
						windowwriter.Attrib("id", "70000");
						windowwriter.Attrib("description", "Financing Requested");
						windowwriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						
						arwindowFin = document.getElementById("SERVICE_MAGIC_window_financing").value.split('|');
						windowwriter.BeginNode("answer");
						windowwriter.Attrib("id", arwindowFin[0]);
						windowwriter.Attrib("questionID", "70000");
						windowwriter.WriteCData(arwindowFin[1]);
						windowwriter.EndNode();  // end answer
						windowwriter.EndNode();  // end question
				    
						windowwriter.BeginNode("question");
						windowwriter.Attrib("id", "160813");
						windowwriter.Attrib("description", "Immediate Service Required");
						windowwriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						
						arwindowSign='';
						for(a=0;a<2;a++)
						{
								teststring = "SERVICE_MAGIC_window_assistance_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arpaintExt =document.getElementById(teststring).value.split('|'); 
								}
						}
						windowwriter.BeginNode("answer");
						windowwriter.Attrib("id", arwindowSign[0]);
						windowwriter.Attrib("questionID", "160813");
						windowwriter.WriteCData(arwindowSign[1]);
						windowwriter.EndNode();  // end answer
						windowwriter.EndNode();  // end question
				    
						windowwriter.BeginNode("question");
						windowwriter.Attrib("id", "50000");
						windowwriter.Attrib("description", "Covered by Insurance");
						windowwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var windowIns = "No";
						var windowInsID = "10002";
						if (document.getElementById("SERVICE_MAGIC_window_insurance").checked)
						{
							windowIns = "Yes";
							windowInsID = "10001";
						}
						//string[] arwindowIns = new string[1];
						//arwindowIns = windowIns.Split(splitter);
						windowwriter.BeginNode("answer");
						windowwriter.Attrib("id", windowInsID);
						windowwriter.Attrib("questionID", "50000");
						windowwriter.WriteCData(windowIns);
						windowwriter.EndNode();  // end answer
						windowwriter.EndNode();  // end question
				    
						windowwriter.BeginNode("question");
						windowwriter.Attrib("id", "10019");
						windowwriter.Attrib("description", "Request for Commercial Location");
						windowwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var windowComm = "No";
						var windowCommID = "10002";
						
						if (document.getElementById("SERVICE_MAGIC_window_commercial").checked)
						{
							windowComm = "Yes";
							windowCommID = "10001";
						}
						//string[] arwindowComm = new string[1];
						//arwindowComm = windowComm.Split(splitter);
						windowwriter.BeginNode("answer");
						windowwriter.Attrib("id", windowCommID);
						windowwriter.Attrib("questionID", "10019");
						windowwriter.WriteCData(windowComm);
						windowwriter.EndNode();  // end answer
						windowwriter.EndNode();  // end question
				    
						//End the interview element
						windowwriter.EndNode();  // end interview
				    
						//End the task element
						windowwriter.EndNode();  // end task
				    
						// end the root element
						windowwriter.EndNode();
				    
						//windowwriter.Flush();         
						//Write the XML to file and close the writer
						windowwriter.Close();  
						document.getElementById("SERVICE_MAGIC_window_XML").value=(windowFile+windowwriter.ToString().replace(/</g,"\n<"));				
						
						
}//endif
if(document.getElementById("Service_Magic_refcab").checked){						

///////////////////////for reface cabinets///////////////////////////////////////////////////////////////
						var refcabFile;
						var refcabwriter = new XMLWriter() ;
						refcabFile = "<?xml version='1.0' encoding='UTF-8'?>";
						refcabFile += "<!DOCTYPE serviceRequest SYSTEM 'http://www.servicemagic.com/dtd/submitServiceRequest.dtd'>";
						//Write the root element
						refcabwriter.BeginNode("serviceRequest");
				    
						//Add an attribute to the previously created element 
						refcabwriter.Attrib("affiliateCode", "silvercarrot3");
						refcabwriter.Attrib("version", "1.0");
						refcabwriter.Attrib("testOnly", "false");
				    
						//Start an element
						refcabwriter.BeginNode("customer");
						refcabwriter.BeginNode("contact");
				    
						refcabwriter.BeginNode("firstName");
						refcabwriter.WriteCData(sFirst);
						refcabwriter.EndNode();  // end firstName
						refcabwriter.BeginNode("lastName");
						refcabwriter.WriteCData(sLast);
						refcabwriter.EndNode();  // end lastName
						
						arrefcabContact = document.getElementById("SERVICE_MAGIC_preferredContactTime").value.split('|');
						refcabwriter.BeginNode("contactTime");
						refcabwriter.WriteCData(arrefcabContact[1]);
						refcabwriter.EndNode();  // end contactTime
				    
						refcabwriter.BeginNode("contactMethods");
				    
						refcabwriter.BeginNode("contactMethod");
						refcabwriter.Attrib("type", "dayPhone");
						refcabwriter.WriteCData(sPhone);
						refcabwriter.EndNode();  // end contactMethod
						if (document.getElementById("SERVICE_MAGIC_cellphone").value)
						{
							refcabwriter.BeginNode("contactMethod");
							refcabwriter.Attrib("type", "cellPhone");
							refcabwriter.WriteCData(document.getElementById("SERVICE_MAGIC_cellphone").value);
							refcabwriter.EndNode();  // end contactMethod
						}
						if (document.getElementById("SERVICE_MAGIC_eveningphone").value)
						{
							refcabwriter.BeginNode("contactMethod");
							refcabwriter.Attrib("type", "eveningPhone");
							refcabwriter.WriteCData(document.getElementById("SERVICE_MAGIC_eveningphone").value);
							refcabwriter.EndNode();  // end contactMethod
						}
						refcabwriter.BeginNode("contactMethod");
						refcabwriter.Attrib("type", "email");
						refcabwriter.WriteCData(sEmail);
						refcabwriter.EndNode();  // end contactMethod
				    
						refcabwriter.EndNode();  // end contactMethods
						refcabwriter.EndNode();  // end contact
				    
						refcabwriter.BeginNode("location"); 
						//add sub-elements
						refcabwriter.BeginNode("addressLine1");
						refcabwriter.WriteCData(sAddress+sAddress2);
						refcabwriter.EndNode();  // end addressLine1
						refcabwriter.BeginNode("city");
						refcabwriter.WriteCData(sCity);
						refcabwriter.EndNode();  // end city
						refcabwriter.BeginNode("zip");
						refcabwriter.WriteCData(sZip);
						refcabwriter.EndNode();  // end zip
				    
						refcabwriter.EndNode();  // end location
						refcabwriter.EndNode();  // end customer
	
						refcabwriter.BeginNode("task");
						//Add an attribute to the previously created element 
						refcabwriter.Attrib("description", "Reface Cabinets");
						refcabwriter.Attrib("oid", "40178");
				    
						refcabwriter.BeginNode("interview");

					    refcabLocation = 0;
						for(a=0;a<7;a++)
						{
								teststring = "SERVICE_MAGIC_refcab_location_"+a;
								refcabLocation |=document.getElementById(teststring).checked; 
						}				    
						if (refcabLocation != 0)
						{
							refcabwriter.BeginNode("question");
							refcabwriter.Attrib("id", "4368");
							refcabwriter.Attrib("description", "Refacing Location(s)");
							refcabwriter.Attrib("type", "TASK_INTERVIEW"); 			    
							for(a=0;a<7;a++)
							{
								teststring = "SERVICE_MAGIC_refcab_location_"+a;
								arrefcabLocation =document.getElementById(teststring).value.split('|');
								
								refcabwriter.BeginNode("answer");
								refcabwriter.Attrib("id", arrefcabLocation[0]);
								refcabwriter.Attrib("questionID", "4368");
								refcabwriter.WriteCData(arrefcabLocation[1]);
								refcabwriter.EndNode();  // end answer 
							}
							refcabwriter.EndNode();  // end question
						}
				    
						refcabwriter.BeginNode("question");
						refcabwriter.Attrib("id", "4370");
						refcabwriter.Attrib("description", "Cabinet Material");
						refcabwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						arrefcabMade='';
						for(a=0;a<5;a++)
						{
								teststring = "SERVICE_MAGIC_refcab_made_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arrefcabMade =document.getElementById(teststring).value.split('|'); 
								}
						}							    
						refcabwriter.BeginNode("answer");
						refcabwriter.Attrib("id", arrefcabMade[0]);
						refcabwriter.Attrib("questionID", "4370");
						refcabwriter.WriteCData(arrefcabMade[1]);
						refcabwriter.EndNode();  // end answer
						refcabwriter.EndNode();  // end question
				    
					    refcabMaterial = 0;
						for(a=0;a<8;a++)
						{
								teststring = "SERVICE_MAGIC_refcab_material_"+a;
								refcabMaterial |=document.getElementById(teststring).checked; 
						}
						if (refcabMaterial != 0)
						{
							refcabwriter.BeginNode("question");
							refcabwriter.Attrib("id", "4371");
							refcabwriter.Attrib("description", "Refacing Material");
							refcabwriter.Attrib("type", "TASK_INTERVIEW"); 			    
							for(a=0;a<8;a++)
							{
								teststring = "SERVICE_MAGIC_refcab_material_"+a;
								arrefcabMaterial =document.getElementById(teststring).value.split('|');
								
								refcabwriter.BeginNode("answer");
								refcabwriter.Attrib("id", arrefcabMaterial[0]);
								refcabwriter.Attrib("questionID", "4371");
								refcabwriter.WriteCData(arrefcabMaterial[1]);
								refcabwriter.EndNode();  // end answer 
							}
							refcabwriter.EndNode();  // end question
						}
				    
						refcabwriter.BeginNode("question");
						refcabwriter.Attrib("id", "90000");
						refcabwriter.Attrib("description", "Request Stage");
						refcabwriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						arrefcabStatus = document.getElementById("SERVICE_MAGIC_refcab_status").value.split('|');
						refcabwriter.BeginNode("answer");
						refcabwriter.Attrib("id", arrefcabStatus[0]);
						refcabwriter.Attrib("questionID", "90000");
						refcabwriter.WriteCData(arrefcabStatus[1]);
						refcabwriter.EndNode();  // end answer
						refcabwriter.EndNode();  // end question
				    
						refcabwriter.BeginNode("question");
						refcabwriter.Attrib("id", "315");
						refcabwriter.Attrib("description", "Cabinet Type");
						refcabwriter.Attrib("type", "TASK_INTERVIEW"); 
						
						arrefcabType='';
						for(a=0;a<4;a++)
						{
								teststring = "SERVICE_MAGIC_refcab_type_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arrefcabType =document.getElementById(teststring).value.split('|'); 
								}
						}
						refcabwriter.BeginNode("answer");
						refcabwriter.Attrib("id", arrefcabType[0]);
						refcabwriter.Attrib("questionID", "315");
						refcabwriter.WriteCData(arrefcabType[1]);
						refcabwriter.EndNode();  // end answer
						refcabwriter.EndNode();  // end question
				    
						refcabwriter.BeginNode("question");
						refcabwriter.Attrib("id", "80000");
						refcabwriter.Attrib("description", "Desired Completion Date");
						refcabwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						arrefcabComp = document.getElementById("SERVICE_MAGIC_refcab_completed").value.split('|');
						refcabwriter.BeginNode("answer");
						refcabwriter.Attrib("id", arrefcabComp[0]);
						refcabwriter.Attrib("questionID", "80000");
						refcabwriter.WriteCData(arrefcabComp[1]);
						refcabwriter.EndNode();  // end answer
						refcabwriter.EndNode();  // end question


					    refcabFeat = 0;
						for(a=0;a<10;a++)
						{
								teststring = "SERVICE_MAGIC_refcab_features_"+a;
								refcabFeat |=document.getElementById(teststring).checked; 
						}				    
						if (refcabFeat != 0)
						{
							refcabwriter.BeginNode("question");
							refcabwriter.Attrib("id", "5530");
							refcabwriter.Attrib("description", "Special Features");
							refcabwriter.Attrib("type", "TASK_INTERVIEW"); 			    
							for(a=0;a<10;a++)
							{
								teststring = "SERVICE_MAGIC_refcab_features_"+a;
								arrefcabFeat =document.getElementById(teststring).value.split('|');

								refcabwriter.BeginNode("answer");
								refcabwriter.Attrib("id", arrefcabFeat[0]);
								refcabwriter.Attrib("questionID", "5530");
								refcabwriter.WriteCData(arrefcabFeat[1]);
								refcabwriter.EndNode();  // end answer 
							}
							refcabwriter.EndNode();  // end question
						}
				    
						refcabwriter.BeginNode("question");
						refcabwriter.Attrib("id", "50000");
						refcabwriter.Attrib("description", "Covered by Insurance");
						refcabwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var refcabIns = "No";
						var refcabInsID = "10002";
						if (document.getElementById("SERVICE_MAGIC_refcab_insurance").checked)
						{
							refcabIns = "Yes";
							refcabInsID = "10001";
						}
						//string[] arrefcabIns = new string[1];
						//arrefcabIns = refcabIns.Split(splitter);
						refcabwriter.BeginNode("answer");
						refcabwriter.Attrib("id", refcabInsID);
						refcabwriter.Attrib("questionID", "50000");
						refcabwriter.WriteCData(refcabIns);
						refcabwriter.EndNode();  // end answer
						refcabwriter.EndNode();  // end question
				    
						refcabwriter.BeginNode("question");
						refcabwriter.Attrib("id", "10019");
						refcabwriter.Attrib("description", "Request for Commercial Location");
						refcabwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var refcabComm = "No";
						var refcabCommID = "10002";
						if (document.getElementById("SERVICE_MAGIC_refcab_commercial").checked)
						{
							refcabComm = "Yes";
							refcabCommID = "10001";
						}
						//string[] arrefcabComm = new string[1];
						//arrefcabComm = refcabComm.Split(splitter);
						refcabwriter.BeginNode("answer");
						refcabwriter.Attrib("id", refcabCommID);
						refcabwriter.Attrib("questionID", "10019");
						refcabwriter.WriteCData(refcabComm);
						refcabwriter.EndNode();  // end answer
						refcabwriter.EndNode();  // end question
				    
						refcabwriter.BeginNode("question");
						refcabwriter.Attrib("id", "1700");
						refcabwriter.Attrib("description", "Consumer Owns Home");
						refcabwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var refcabOwn = "No";
						var refcabOwnID = "10002";
						if (document.getElementById("SERVICE_MAGIC_refcab_own_home").checked)
						{
							refcabOwn = "Yes";
							refcabOwnID = "10001";
						}
						//string[] arrefcabComm = new string[1];
						//arrefcabComm = refcabComm.Split(splitter);
						refcabwriter.BeginNode("answer");
						refcabwriter.Attrib("id", refcabOwnID);
						refcabwriter.Attrib("questionID", "1700");
						refcabwriter.WriteCData(refcabOwn);
						refcabwriter.EndNode();  // end answer
						refcabwriter.EndNode();  // end question
				    
						//End the interview element
						refcabwriter.EndNode();  // end interview
				    
						//End the task element
						refcabwriter.EndNode();  // end task
				    
						// end the root element
						refcabwriter.EndNode();
				    
						//refcabwriter.Flush();         
						//Write the XML to file and close the writer
						refcabwriter.Close();  
						document.getElementById("SERVICE_MAGIC_refcab_XML").value=(refcabFile+refcabwriter.ToString().replace(/</g,"\n<"));				
						
}//endif
if(document.getElementById("Service_Magic_instcab").checked){						

///////////////////////for install cabinets///////////////////////////////////////////////////////////////
						var instcabFile;
						var instcabwriter = new XMLWriter() ;
						instcabFile = "<?xml version='1.0' encoding='UTF-8'?>";
						instcabFile += "<!DOCTYPE serviceRequest SYSTEM 'http://www.servicemagic.com/dtd/submitServiceRequest.dtd'>";
						//Write the root element
						instcabwriter.BeginNode("serviceRequest");
				    
						//Add an attribute to the previously created element 
						instcabwriter.Attrib("affiliateCode", "silvercarrot3");
						instcabwriter.Attrib("version", "1.0");
						instcabwriter.Attrib("testOnly", "false");
				    
						//Start an element
						instcabwriter.BeginNode("customer");
						instcabwriter.BeginNode("contact");
				    
						instcabwriter.BeginNode("firstName");
						instcabwriter.WriteCData(sFirst);
						instcabwriter.EndNode();  // end firstName
						instcabwriter.BeginNode("lastName");
						instcabwriter.WriteCData(sLast);
						instcabwriter.EndNode();  // end lastName
						arinstcabContact = document.getElementById("SERVICE_MAGIC_preferredContactTime").value.split('|');
						instcabwriter.BeginNode("contactTime");
						instcabwriter.WriteCData(arinstcabContact[1]);
						instcabwriter.EndNode();  // end contactTime
				    
						instcabwriter.BeginNode("contactMethods");
				    
						instcabwriter.BeginNode("contactMethod");
						instcabwriter.Attrib("type", "dayPhone");
						instcabwriter.WriteCData(sPhone);
						instcabwriter.EndNode();  // end contactMethod
						if (document.getElementById("SERVICE_MAGIC_cellphone").value)
						{
							instcabwriter.BeginNode("contactMethod");

							instcabwriter.Attrib("type", "cellPhone");
							instcabwriter.WriteCData(document.getElementById("SERVICE_MAGIC_cellphone").value);
							instcabwriter.EndNode();  // end contactMethod
						}
						if (document.getElementById("SERVICE_MAGIC_eveningphone").value)
						{
							instcabwriter.BeginNode("contactMethod");
							instcabwriter.Attrib("type", "eveningPhone");
							instcabwriter.WriteCData(document.getElementById("SERVICE_MAGIC_eveningphone").value);
							instcabwriter.EndNode();  // end contactMethod
						}
						instcabwriter.BeginNode("contactMethod");
						instcabwriter.Attrib("type", "email");
						instcabwriter.WriteCData(sEmail);
						instcabwriter.EndNode();  // end contactMethod
				    
						instcabwriter.EndNode();  // end contactMethods
						instcabwriter.EndNode();  // end contact
				    
						instcabwriter.BeginNode("location"); 
						//add sub-elements
						instcabwriter.BeginNode("addressLine1");
						instcabwriter.WriteCData(sAddress+sAddress2);
						instcabwriter.EndNode();  // end addressLine1
						instcabwriter.BeginNode("city");
						instcabwriter.WriteCData(sCity);
						instcabwriter.EndNode();  // end city
						instcabwriter.BeginNode("zip");
						instcabwriter.WriteCData(sZip);
						instcabwriter.EndNode();  // end zip
				    
						instcabwriter.EndNode();  // end location
						instcabwriter.EndNode();  // end customer
	
						instcabwriter.BeginNode("task");
						//Add an attribute to the previously created element 
						instcabwriter.Attrib("description", "Install Cabinets");
						instcabwriter.Attrib("oid", "40003");
				    
						instcabwriter.BeginNode("interview");

					    instcabServices = 0;
						for(a=0;a<5;a++)
						{
								teststring = "SERVICE_MAGIC_instcab_services_"+a;
								instcabServices |=document.getElementById(teststring).checked; 
						}
						if (instcabServices != 0)
						{
							instcabwriter.BeginNode("question");
							instcabwriter.Attrib("id", "5517");
							instcabwriter.Attrib("description", "Services Needed");
							instcabwriter.Attrib("type", "TASK_INTERVIEW"); 	
							
									    
							for(a=0;a<5;a++)
							{
								teststring = "SERVICE_MAGIC_instcab_services_"+a;
								arinstcabServices =document.getElementById(teststring).value.split('|');
							
								instcabwriter.BeginNode("answer");
								instcabwriter.Attrib("id", arinstcabServices[0]);
								instcabwriter.Attrib("questionID", "5517");
								instcabwriter.WriteCData(arinstcabServices[1]);
								instcabwriter.EndNode();  // end answer 
							}
							instcabwriter.EndNode();  // end question
						}
				    
					
					    instcabLocation = 0;
						for(a=0;a<12;a++)
						{
								teststring = "SERVICE_MAGIC_instcab_location_"+a;
								instcabLocation |=document.getElementById(teststring).checked; 
						}					
						
						if (instcabLocation != 0)
						{
							instcabwriter.BeginNode("question");
							instcabwriter.Attrib("id", "307");
							instcabwriter.Attrib("description", "Location for Cabinets");
							instcabwriter.Attrib("type", "TASK_INTERVIEW"); 			    
							for(a=0;a<12;a++)
							{
								teststring = "SERVICE_MAGIC_instcab_location_"+a;
								arinstcabLocation =document.getElementById(teststring).value.split('|');

								instcabwriter.BeginNode("answer");
								instcabwriter.Attrib("id", arinstcabLocation[0]);
								instcabwriter.Attrib("questionID", "307");
								instcabwriter.WriteCData(arinstcabLocation[1]);
								instcabwriter.EndNode();  // end answer 
							}
							instcabwriter.EndNode();  // end question
						}
				    
						instcabwriter.BeginNode("question");
						instcabwriter.Attrib("id", "308");
						instcabwriter.Attrib("description", "Cabinet Material");
						instcabwriter.Attrib("type", "TASK_INTERVIEW"); 		


						arinstcabMade='';
						for(a=0;a<9;a++)
						{
								teststring = "SERVICE_MAGIC_instcab_made_"+a;	
								if(document.getElementById(teststring).checked)
								{
									arinstcabMade =document.getElementById(teststring).value.split('|'); 
								}
						}														  
						instcabwriter.BeginNode("answer");
						instcabwriter.Attrib("id", arinstcabMade[0]);
						instcabwriter.Attrib("questionID", "308");
						instcabwriter.WriteCData(arinstcabMade[1]);
						instcabwriter.EndNode();  // end answer
						instcabwriter.EndNode();  // end question
				    
						instcabwriter.BeginNode("question");
						instcabwriter.Attrib("id", "90000");
						instcabwriter.Attrib("description", "Request Stage");
						instcabwriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						arinstcabStatus = document.getElementById("SERVICE_MAGIC_instcab_status").value.split('|');
						instcabwriter.BeginNode("answer");
						instcabwriter.Attrib("id", arinstcabStatus[0]);
						instcabwriter.Attrib("questionID", "90000");
						instcabwriter.WriteCData(arinstcabStatus[1]);
						instcabwriter.EndNode();  // end answer
						instcabwriter.EndNode();  // end question
				    
					    instcabFeat = 0;
						for(a=0;a<9;a++)
						{
								teststring = "SERVICE_MAGIC_instcab_features_"+a;
								instcabFeat |=document.getElementById(teststring).checked; 
						}					
						if (instcabFeat != 0)
						{
							instcabwriter.BeginNode("question");
							instcabwriter.Attrib("id", "5518");
							instcabwriter.Attrib("description", "Cabinetry Features");
							instcabwriter.Attrib("type", "TASK_INTERVIEW"); 			    
							for(a=0;a<9;a++)
							{
								teststring = "SERVICE_MAGIC_instcab_features_"+a;
								arinstcabFeat =document.getElementById(teststring).value.split('|');

								instcabwriter.BeginNode("answer");
								instcabwriter.Attrib("id", arinstcabFeat[0]);
								instcabwriter.Attrib("questionID", "5518");
								instcabwriter.WriteCData(arinstcabFeat[1]);
								instcabwriter.EndNode();  // end answer 
							}
							instcabwriter.EndNode();  // end question
						}
				    
						instcabwriter.BeginNode("question");
						instcabwriter.Attrib("id", "80000");
						instcabwriter.Attrib("description", "Desired Completion Date");
						instcabwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						arinstcabComp = document.getElementById("SERVICE_MAGIC_instcab_completed").value.split('|');					
						instcabwriter.BeginNode("answer");
						instcabwriter.Attrib("id", arinstcabComp[0]);
						instcabwriter.Attrib("questionID", "80000");
						instcabwriter.WriteCData(arinstcabComp[1]);
						instcabwriter.EndNode();  // end answer
						instcabwriter.EndNode();  // end question
				    
						instcabwriter.BeginNode("question");
						instcabwriter.Attrib("id", "40000");
						instcabwriter.Attrib("description", "Materials Provided by Homeowner");
						instcabwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var instcabIns = "No";
						var instcabInsID = "10002";
						
						if (document.getElementById("SERVICE_MAGIC_instcab_materials").checked)
						{
							instcabIns = "Yes";
							instcabInsID = "10001";
						}
						//string[] arinstcabIns = new string[1];
						//arinstcabIns = instcabIns.Split(splitter);
						instcabwriter.BeginNode("answer");
						instcabwriter.Attrib("id", instcabInsID);
						instcabwriter.Attrib("questionID", "40000");
						instcabwriter.WriteCData(instcabIns);
						instcabwriter.EndNode();  // end answer
						instcabwriter.EndNode();  // end question
				    
						instcabwriter.BeginNode("question");
						instcabwriter.Attrib("id", "10019");
						instcabwriter.Attrib("description", "Request for Commercial Location");
						instcabwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var instcabComm = "No";
						var instcabCommID = "10002";
						if (document.getElementById("SERVICE_MAGIC_instcab_commercial").checked)
						{
							instcabComm = "Yes";
							instcabCommID = "10001";
						}
						//string[] arinstcabComm = new string[1];
						//arinstcabComm = instcabComm.Split(splitter);
						instcabwriter.BeginNode("answer");
						instcabwriter.Attrib("id", instcabCommID);
						instcabwriter.Attrib("questionID", "10019");
						instcabwriter.WriteCData(instcabComm);
						instcabwriter.EndNode();  // end answer
						instcabwriter.EndNode();  // end question
				    
						instcabwriter.BeginNode("question");
						instcabwriter.Attrib("id", "1700");
						instcabwriter.Attrib("description", "Consumer Owns Home");
						instcabwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var instcabOwn = "No";
						var instcabOwnID = "10002";
						if (document.getElementById("SERVICE_MAGIC_instcab_own_home").checked)
						{
							instcabOwn = "Yes";
							instcabOwnID = "10001";
						}
						//string[] arinstcabComm = new string[1];
						//arinstcabComm = instcabComm.Split(splitter);
						instcabwriter.BeginNode("answer");
						instcabwriter.Attrib("id", instcabOwnID);
						instcabwriter.Attrib("questionID", "1700");
						instcabwriter.WriteCData(instcabOwn);
						instcabwriter.EndNode();  // end answer
						instcabwriter.EndNode();  // end question
				    
						//End the interview element
						instcabwriter.EndNode();  // end interview
				    
						//End the task element
						instcabwriter.EndNode();  // end task
				    
						// end the root element
						instcabwriter.EndNode();
				    
						//instcabwriter.Flush();         
						//Write the XML to file and close the writer
						instcabwriter.Close();  
						document.getElementById("SERVICE_MAGIC_instcab_XML").value=(instcabFile+instcabwriter.ToString().replace(/</g,"\n<"));				
						
}//endif
if(document.getElementById("Service_Magic_alarm").checked){						
	
///////////////////////for install alarm/security system///////////////////////////////////////////////////////////////
						var alarmFile;
						var alarmwriter = new XMLWriter() ;
						alarmFile = "<?xml version='1.0' encoding='UTF-8'?>";
						alarmFile += "<!DOCTYPE serviceRequest SYSTEM 'http://www.servicemagic.com/dtd/submitServiceRequest.dtd'>";
						//Write the root element
						alarmwriter.BeginNode("serviceRequest");
				    
						//Add an attribute to the previously created element 
						alarmwriter.Attrib("affiliateCode", "silvercarrot3");
						alarmwriter.Attrib("version", "1.0");
						alarmwriter.Attrib("testOnly", "false");
				    
						//Start an element
						alarmwriter.BeginNode("customer");
						alarmwriter.BeginNode("contact");
				    
						alarmwriter.BeginNode("firstName");
						alarmwriter.WriteCData(sFirst);
						alarmwriter.EndNode();  // end firstName
						alarmwriter.BeginNode("lastName");
						alarmwriter.WriteCData(sLast);
						alarmwriter.EndNode();  // end lastName
						aralarmContact = document.getElementById("SERVICE_MAGIC_preferredContactTime").value.split('|');
						alarmwriter.BeginNode("contactTime");
						alarmwriter.WriteCData(aralarmContact[1]);
						alarmwriter.EndNode();  // end contactTime
				    
						alarmwriter.BeginNode("contactMethods");
				    
						alarmwriter.BeginNode("contactMethod");
						alarmwriter.Attrib("type", "dayPhone");
						alarmwriter.WriteCData(sPhone);
						alarmwriter.EndNode();  // end contactMethod

						if (document.getElementById("SERVICE_MAGIC_cellphone").value)
						{
							alarmwriter.BeginNode("contactMethod");
							alarmwriter.Attrib("type", "cellPhone");
							alarmwriter.WriteCData(document.getElementById("SERVICE_MAGIC_cellphone").value);
							alarmwriter.EndNode();  // end contactMethod
						}
						if (document.getElementById("SERVICE_MAGIC_eveningphone").value)
						{
							alarmwriter.BeginNode("contactMethod");
							alarmwriter.Attrib("type", "eveningPhone");
							alarmwriter.WriteCData(document.getElementById("SERVICE_MAGIC_eveningphone").value);
							alarmwriter.EndNode();  // end contactMethod
						}

						alarmwriter.BeginNode("contactMethod");
						alarmwriter.Attrib("type", "email");
						alarmwriter.WriteCData(sEmail);
						alarmwriter.EndNode();  // end contactMethod
				    
						alarmwriter.EndNode();  // end contactMethods
						alarmwriter.EndNode();  // end contact
				    
						alarmwriter.BeginNode("location"); 
						//add sub-elements
						alarmwriter.BeginNode("addressLine1");
						alarmwriter.WriteCData(sAddress+sAddress2);
						alarmwriter.EndNode();  // end addressLine1
						alarmwriter.BeginNode("city");
						alarmwriter.WriteCData(sCity);
						alarmwriter.EndNode();  // end city
						alarmwriter.BeginNode("zip");
						alarmwriter.WriteCData(sZip);
						alarmwriter.EndNode();  // end zip
				    
						alarmwriter.EndNode();  // end location
						alarmwriter.EndNode();  // end customer
	
						alarmwriter.BeginNode("task");
						//Add an attribute to the previously created element 
						alarmwriter.Attrib("description", "Install or Replace Security System");
						alarmwriter.Attrib("oid", "40136");
				    
						alarmwriter.BeginNode("interview");
				    
						alarmwriter.BeginNode("question");
						alarmwriter.Attrib("id", "4009");
						alarmwriter.Attrib("description", "Service Needed");
						alarmwriter.Attrib("type", "TASK_INTERVIEW"); 			
						aralarmService='';
						for(a=0;a<3;a++)
						{
								teststring = "SERVICE_MAGIC_alarm_service_"+a;	
								if(document.getElementById(teststring).checked)
								{
									aralarmService =document.getElementById(teststring).value.split('|'); 
								}
						}							    
						
						alarmwriter.BeginNode("answer");
						alarmwriter.Attrib("id", aralarmService[0]);
						alarmwriter.Attrib("questionID", "4009");
						alarmwriter.WriteCData(aralarmService[1]);
						alarmwriter.EndNode();  // end answer
						alarmwriter.EndNode();  // end question
						
					    alarmFeat = 0;
						for(a=0;a<10;a++)
						{
								teststring = "SERVICE_MAGIC_alarm_features_"+a;
								alarmFeat |=document.getElementById(teststring).checked; 
						}				    
						if (alarmFeat != 0)
						{
							alarmwriter.BeginNode("question");
							alarmwriter.Attrib("id", "4010");
							alarmwriter.Attrib("description", "Security Features");
							alarmwriter.Attrib("type", "TASK_INTERVIEW"); 			    
							for(a=0;a<10;a++)
							{
								teststring = "SERVICE_MAGIC_alarm_features_"+a;
								aralarmFeat =document.getElementById(teststring).value.split('|');
								
								alarmwriter.BeginNode("answer");
								alarmwriter.Attrib("id", aralarmFeat[0]);
								alarmwriter.Attrib("questionID", "4010");
								alarmwriter.WriteCData(aralarmFeat[1]);
								alarmwriter.EndNode();  // end answer 
							}
							alarmwriter.EndNode();  // end question
						}
				    
						alarmwriter.BeginNode("question");
						alarmwriter.Attrib("id", "4011");
						alarmwriter.Attrib("description", "Keypad Location");
						alarmwriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						aralarmKeypad='';
						for(a=0;a<5;a++)
						{
								teststring = "SERVICE_MAGIC_alarm_keypad_"+a;	
								if(document.getElementById(teststring).checked)
								{
									aralarmKeypad =document.getElementById(teststring).value.split('|'); 
								}
						}								    
						alarmwriter.BeginNode("answer");
						alarmwriter.Attrib("id", aralarmKeypad[0]);
						alarmwriter.Attrib("questionID", "4011");
						alarmwriter.WriteCData(aralarmKeypad[1]);
						alarmwriter.EndNode();  // end answer
						alarmwriter.EndNode();  // end question
				    
						alarmwriter.BeginNode("question");
						alarmwriter.Attrib("id", "90000");
						alarmwriter.Attrib("description", "Request Stage");
						alarmwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						aralarmStatus = document.getElementById("SERVICE_MAGIC_alarm_status").value.split('|');
						alarmwriter.BeginNode("answer");
						alarmwriter.Attrib("id", aralarmStatus[0]);
						alarmwriter.Attrib("questionID", "90000");
						alarmwriter.WriteCData(aralarmStatus[1]);
						alarmwriter.EndNode();  // end answer
						alarmwriter.EndNode();  // end question
				    
						alarmwriter.BeginNode("question");
						alarmwriter.Attrib("id", "4012");
						alarmwriter.Attrib("description", "Wall Access");
						alarmwriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						aralarmWall='';
						for(a=0;a<5;a++)
						{
								teststring = "SERVICE_MAGIC_alarm_wall_"+a;	
								if(document.getElementById(teststring).checked)
								{
									aralarmWall =document.getElementById(teststring).value.split('|'); 
								}
						}									    
						alarmwriter.BeginNode("answer");
						alarmwriter.Attrib("id", aralarmWall[0]);
						alarmwriter.Attrib("questionID", "4012");
						alarmwriter.WriteCData(aralarmWall[1]);
						alarmwriter.EndNode();  // end answer
						alarmwriter.EndNode();  // end question
				    
						alarmwriter.BeginNode("question");
						alarmwriter.Attrib("id", "80000");
						alarmwriter.Attrib("description", "Desired Completion Date");
						alarmwriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						
						aralarmComp = document.getElementById("SERVICE_MAGIC_alarm_completed").value.split('|');
						alarmwriter.BeginNode("answer");
						alarmwriter.Attrib("id", aralarmComp[0]);
						alarmwriter.Attrib("questionID", "80000");
						alarmwriter.WriteCData(aralarmComp[1]);
						alarmwriter.EndNode();  // end answer
						alarmwriter.EndNode();  // end question
					
						var alarmNum1 = document.getElementById("SERVICE_MAGIC_alarm_brand1097").value;
						var alarmNum2 = document.getElementById("SERVICE_MAGIC_alarm_age704").value;
						
						if (alarmNum1 != "" || alarmNum2 != "")
						{
							alarmwriter.BeginNode("question");
							alarmwriter.Attrib("id", "5569");
							alarmwriter.Attrib("description", "Existing System-Brand/Age");
							alarmwriter.Attrib("type", "TASK_INTERVIEW"); 			    
							if (alarmNum1 != "")
							{
								alarmwriter.BeginNode("answer");
								alarmwriter.Attrib("id", "1097");
								alarmwriter.Attrib("questionID", "5569");
								alarmwriter.WriteCData(alarmNum1);
								alarmwriter.EndNode();  // end answer 
							}
							if (alarmNum2 != "")
							{
								alarmwriter.BeginNode("answer");
								alarmwriter.Attrib("id", "704");
								alarmwriter.Attrib("questionID", "5569");
								alarmwriter.WriteCData(alarmNum2);
								alarmwriter.EndNode();  // end answer 
							}
							alarmwriter.EndNode();  // end question
						}
				    
						alarmwriter.BeginNode("question");
						alarmwriter.Attrib("id", "30000");
						alarmwriter.Attrib("description", "Historical Work");
						alarmwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var alarmHist = "No";
						var alarmHistID = "10002";
						if (document.getElementById("SERVICE_MAGIC_alarm_historical").checked)
						{
							alarmHist = "Yes";
							alarmHistID = "10001";
						}
						//string[] aralarmIns = new string[1];
						//aralarmIns = alarmIns.Split(splitter);
						alarmwriter.BeginNode("answer");
						alarmwriter.Attrib("id", alarmHistID);
						alarmwriter.Attrib("questionID", "30000");
						alarmwriter.WriteCData(alarmHist);
						alarmwriter.EndNode();  // end answer
						alarmwriter.EndNode();  // end question
				    
						alarmwriter.BeginNode("question");
						alarmwriter.Attrib("id", "50000");
						alarmwriter.Attrib("description", "Covered by Insurance");
						alarmwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var alarmIns = "No";
						var alarmInsID = "10002";
						if (document.getElementById("SERVICE_MAGIC_alarm_insurance").checked)
						{
							alarmIns = "Yes";
							alarmInsID = "10001";
						}
						//string[] aralarmIns = new string[1];
						//aralarmIns = alarmIns.Split(splitter);
						alarmwriter.BeginNode("answer");
						alarmwriter.Attrib("id", alarmInsID);
						alarmwriter.Attrib("questionID", "50000");
						alarmwriter.WriteCData(alarmIns);
						alarmwriter.EndNode();  // end answer
						alarmwriter.EndNode();  // end question
				    
						alarmwriter.BeginNode("question");
						alarmwriter.Attrib("id", "10019");
						alarmwriter.Attrib("description", "Request for Commercial Location");
						alarmwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var alarmComm = "No";
						var alarmCommID = "10002";
					
						if (document.getElementById("SERVICE_MAGIC_alarm_commercial").checked)
						{
							alarmComm = "Yes";
							alarmCommID = "10001";
						}
						//string[] aralarmComm = new string[1];
						//aralarmComm = alarmComm.Split(splitter);
						alarmwriter.BeginNode("answer");
						alarmwriter.Attrib("id", alarmCommID);
						alarmwriter.Attrib("questionID", "10019");
						alarmwriter.WriteCData(alarmComm);
						alarmwriter.EndNode();  // end answer
						alarmwriter.EndNode();  // end question
				    
						alarmwriter.BeginNode("question");
						alarmwriter.Attrib("id", "1700");
						alarmwriter.Attrib("description", "Consumer Owns Home");
						alarmwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var alarmOwn = "No";
						var alarmOwnID = "10002";
						
						if (document.getElementById("SERVICE_MAGIC_alarm_own_home").checked	)
						{
							alarmOwn = "Yes";
							alarmOwnID = "10001";
						}
						//string[] aralarmComm = new string[1];
						//aralarmComm = alarmComm.Split(splitter);
						alarmwriter.BeginNode("answer");
						alarmwriter.Attrib("id", alarmOwnID);
						alarmwriter.Attrib("questionID", "1700");
						alarmwriter.WriteCData(alarmOwn);
						alarmwriter.EndNode();  // end answer
						alarmwriter.EndNode();  // end question
				    
						//End the interview element
						alarmwriter.EndNode();  // end interview
				    
						//End the task element
						alarmwriter.EndNode();  // end task
				    
						// end the root element
						alarmwriter.EndNode();
				    
						//alarmwriter.Flush();         
						//Write the XML to file and close the writer
						alarmwriter.Close();  
						document.getElementById("SERVICE_MAGIC_alarm_XML").value=(alarmFile+alarmwriter.ToString().replace(/</g,"\n<"));				
						
}//endif
if(document.getElementById("Service_Magic_maid").checked){						

///////////////////////for maid services///////////////////////////////////////////////////////////////
						var maidFile;
						var maidwriter = new XMLWriter() ;
						maidFile = "<?xml version='1.0' encoding='UTF-8'?>";
						maidFile += "<!DOCTYPE serviceRequest SYSTEM 'http://www.servicemagic.com/dtd/submitServiceRequest.dtd'>";
						//Write the root element
						maidwriter.BeginNode("serviceRequest");
				    
						//Add an attribute to the previously created element 
						maidwriter.Attrib("affiliateCode", "silvercarrot3");
						maidwriter.Attrib("version", "1.0");
						maidwriter.Attrib("testOnly", "false");
				    
						//Start an element
						maidwriter.BeginNode("customer");
						maidwriter.BeginNode("contact");
				    
						maidwriter.BeginNode("firstName");
						maidwriter.WriteCData(sFirst);
						maidwriter.EndNode();  // end firstName
						maidwriter.BeginNode("lastName");
						maidwriter.WriteCData(sLast);
						maidwriter.EndNode();  // end lastName
						armaidContact= document.getElementById("SERVICE_MAGIC_preferredContactTime").value.split('|');
						maidwriter.BeginNode("contactTime");
						maidwriter.WriteCData(armaidContact[1]);
						maidwriter.EndNode();  // end contactTime
				    
						maidwriter.BeginNode("contactMethods");
				    
						maidwriter.BeginNode("contactMethod");
						maidwriter.Attrib("type", "dayPhone");
						maidwriter.WriteCData(sPhone);
						maidwriter.EndNode();  // end contactMethod
						if (document.getElementById("SERVICE_MAGIC_cellphone").value)
						{
							maidwriter.BeginNode("contactMethod");
							maidwriter.Attrib("type", "cellPhone");
							maidwriter.WriteCData(document.getElementById("SERVICE_MAGIC_cellphone").value);
							maidwriter.EndNode();  // end contactMethod
						}
						if (document.getElementById("SERVICE_MAGIC_eveningphone").value)
						{
							maidwriter.BeginNode("contactMethod");
							maidwriter.Attrib("type", "eveningPhone");
							maidwriter.WriteCData(document.getElementById("SERVICE_MAGIC_eveningphone").value);
							maidwriter.EndNode();  // end contactMethod
						}
						maidwriter.BeginNode("contactMethod");
						maidwriter.Attrib("type", "email");
						maidwriter.WriteCData(sEmail);
						maidwriter.EndNode();  // end contactMethod
				    
						maidwriter.EndNode();  // end contactMethods
						maidwriter.EndNode();  // end contact
				    
						maidwriter.BeginNode("location"); 
						//add sub-elements
						maidwriter.BeginNode("addressLine1");
						maidwriter.WriteCData(sAddress+sAddress2);
						maidwriter.EndNode();  // end addressLine1
						maidwriter.BeginNode("city");
						maidwriter.WriteCData(sCity);
						maidwriter.EndNode();  // end city
						maidwriter.BeginNode("zip");
						maidwriter.WriteCData(sZip);
						maidwriter.EndNode();  // end zip
				    
						maidwriter.EndNode();  // end location
						maidwriter.EndNode();  // end customer
	
						maidwriter.BeginNode("task");
						//Add an attribute to the previously created element 
						maidwriter.Attrib("description", "Maid Service");
						maidwriter.Attrib("oid", "40006");
				    
						maidwriter.BeginNode("interview");
				    
						maidwriter.BeginNode("question");
						maidwriter.Attrib("id", "767");
						maidwriter.Attrib("description", "Cleaning Type Needed");
						maidwriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						armaidType='';
						for(a=0;a<8;a++)
						{
								teststring = "SERVICE_MAGIC_maid_type_"+a;	
								if(document.getElementById(teststring).checked)
								{
									armaidType =document.getElementById(teststring).value.split('|'); 
								}
						}			
						maidwriter.BeginNode("answer");
						maidwriter.Attrib("id", armaidType[0]);
						maidwriter.Attrib("questionID", "767");
						maidwriter.WriteCData(armaidType[1]);
						maidwriter.EndNode();  // end answer
						maidwriter.EndNode();  // end question
				    
						maidwriter.BeginNode("question");
						maidwriter.Attrib("id", "90000");
						maidwriter.Attrib("description", "Request Stage");
						maidwriter.Attrib("type", "TASK_INTERVIEW"); 
						
						armaidStatus = document.getElementById("SERVICE_MAGIC_maid_status").value.split('|');
						maidwriter.BeginNode("answer");
						maidwriter.Attrib("id", armaidStatus[0]);
						maidwriter.Attrib("questionID", "90000");
						maidwriter.WriteCData(armaidStatus[1]);
						maidwriter.EndNode();  // end answer
						maidwriter.EndNode();  // end question
				    
						maidwriter.BeginNode("question");
						maidwriter.Attrib("id", "80000");
						maidwriter.Attrib("description", "Desired Completion Date");
						maidwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						armaidComp = document.getElementById("SERVICE_MAGIC_maid_completed").value.split('|');
						maidwriter.BeginNode("answer");
						maidwriter.Attrib("id", armaidComp[0]);
						maidwriter.Attrib("questionID", "80000");
						maidwriter.WriteCData(armaidComp[1]);
						maidwriter.EndNode();  // end answer
						maidwriter.EndNode();  // end question
				    
						maidwriter.BeginNode("question");
						maidwriter.Attrib("id", "160810");
						maidwriter.Attrib("description", "Recurring Service Requested");
						maidwriter.Attrib("type", "TASK_INTERVIEW"); 	
						
						armaidRecur = document.getElementById("SERVICE_MAGIC_maid_recurring").value.split('|');
						maidwriter.BeginNode("answer");
						maidwriter.Attrib("id", armaidRecur[0]);
						maidwriter.Attrib("questionID", "160810");
						maidwriter.WriteCData(armaidRecur[1]);
						maidwriter.EndNode();  // end answer
						maidwriter.EndNode();  // end question
				    
					

						var maidFoot = document.getElementById("SERVICE_MAGIC_maid_footage").value;
						if (maidFoot != "")
						{
							maidwriter.BeginNode("question");
							maidwriter.Attrib("id", "5289");
							maidwriter.Attrib("description", "House Size");
							maidwriter.Attrib("type", "TASK_INTERVIEW"); 			    
							if (maidFoot != "")
							{
								maidwriter.BeginNode("answer");
								maidwriter.Attrib("id", "0");
								maidwriter.Attrib("questionID", "5289");
								maidwriter.WriteCData(maidFoot);
								maidwriter.EndNode();  // end answer 
							}
							maidwriter.EndNode();  // end question
						}
				    
						var maidNum1 = document.getElementById("SERVICE_MAGIC_maid_bedrooms1006").value;
						var maidNum2 = document.getElementById("SERVICE_MAGIC_maid_bathrooms987").value;
						if (maidNum1 != "" || maidNum2 != "")
						{
							maidwriter.BeginNode("question");
							maidwriter.Attrib("id", "5783");
							maidwriter.Attrib("description", "Bedrooms / Bathrooms");
							maidwriter.Attrib("type", "TASK_INTERVIEW"); 			    
							if (maidNum1 != "")
							{
								maidwriter.BeginNode("answer");
								maidwriter.Attrib("id", "1006");
								maidwriter.Attrib("questionID", "5783");
								maidwriter.WriteCData(maidNum1);
								maidwriter.EndNode();  // end answer 
							}
							if (maidNum2 != "")
							{
								maidwriter.BeginNode("answer");
								maidwriter.Attrib("id", "987");
								maidwriter.Attrib("questionID", "5783");
								maidwriter.WriteCData(maidNum2);
								maidwriter.EndNode();  // end answer 
							}
							maidwriter.EndNode();  // end question
						}
						
					    maidServ = 0;
						for(a=0;a<16;a++)
						{
								teststring = "SERVICE_MAGIC_maid_services_"+a;
								maidServ |=document.getElementById(teststring).checked; 
						}						
						if (maidServ != 0)
						{
							maidwriter.BeginNode("question");
							maidwriter.Attrib("id", "778");
							maidwriter.Attrib("description", "Cleaning Services");
							maidwriter.Attrib("type", "TASK_INTERVIEW"); 			    
							for(a=0;a<16;a++)
							{
								teststring = "SERVICE_MAGIC_maid_services_"+a;
								armaidServ =document.getElementById(teststring).value.split('|');

								maidwriter.BeginNode("answer");
								maidwriter.Attrib("id", armaidServ[0]);
								maidwriter.Attrib("questionID", "778");
								maidwriter.WriteCData(armaidServ[1]);
								maidwriter.EndNode();  // end answer 
							}
							maidwriter.EndNode();  // end question
						}
				    
						maidwriter.BeginNode("question");
						maidwriter.Attrib("id", "5792");
						maidwriter.Attrib("description", "Pets");
						maidwriter.Attrib("type", "TASK_INTERVIEW"); 			    

						armaidPets='';
						for(a=0;a<2;a++)
						{
								teststring = "SERVICE_MAGIC_maid_pets_"+a;	
								if(document.getElementById(teststring).checked)
								{
									armaidPets =document.getElementById(teststring).value.split('|'); 
								}
						}							
						
						maidwriter.BeginNode("answer");
						maidwriter.Attrib("id", armaidPets[0]);
						maidwriter.Attrib("questionID", "5792");
						maidwriter.WriteCData(armaidPets[1]);
						maidwriter.EndNode();  // end answer
						maidwriter.EndNode();  // end question
				    
						maidwriter.BeginNode("question");
						maidwriter.Attrib("id", "769");
						maidwriter.Attrib("description", "Residence Type");
						maidwriter.Attrib("type", "TASK_INTERVIEW"); 		
						
						armaidRes='';
						for(a=0;a<3;a++)
						{
								teststring = "SERVICE_MAGIC_maid_residence_"+a;	
								if(document.getElementById(teststring).checked)
								{
									armaidRes =document.getElementById(teststring).value.split('|'); 
								}
						}								    
						maidwriter.BeginNode("answer");
						maidwriter.Attrib("id", armaidRes[0]);
						maidwriter.Attrib("questionID", "769");
						maidwriter.WriteCData(armaidRes[1]);
						maidwriter.EndNode();  // end answer
						maidwriter.EndNode();  // end question
				    
						maidwriter.BeginNode("question");
						maidwriter.Attrib("id", "5253");
						maidwriter.Attrib("description", "Cleaning Supplies Provided");
						maidwriter.Attrib("type", "TASK_INTERVIEW"); 
						
						armaidSupplies='';
						for(a=0;a<2;a++)
						{
								teststring = "SERVICE_MAGIC_maid_supplies_"+a;	
								if(document.getElementById(teststring).checked)
								{
									armaidSupplies =document.getElementById(teststring).value.split('|'); 
								}
						}										    
						maidwriter.BeginNode("answer");
						maidwriter.Attrib("id", armaidSupplies[0]);
						maidwriter.Attrib("questionID", "5253");
						maidwriter.WriteCData(armaidSupplies[1]);
						maidwriter.EndNode();  // end answer
						maidwriter.EndNode();  // end question
				    
						maidwriter.BeginNode("question");
						maidwriter.Attrib("id", "1700");
						maidwriter.Attrib("description", "Consumer Owns Home");
						maidwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var maidOwn = "No";
						var maidOwnID = "10002";
						if (document.getElementById("SERVICE_MAGIC_maid_own_home").checked)
						{
							maidOwn = "Yes";
							maidOwnID = "10001";
						}
						//string[] armaidComm = new string[1];
						//armaidComm = maidComm.Split(splitter);
						maidwriter.BeginNode("answer");
						maidwriter.Attrib("id", maidOwnID);
						maidwriter.Attrib("questionID", "1700");
						maidwriter.WriteCData(maidOwn);
						maidwriter.EndNode();  // end answer
						maidwriter.EndNode();  // end question
				    
						maidwriter.BeginNode("question");
						maidwriter.Attrib("id", "10019");
						maidwriter.Attrib("description", "Request for Commercial Location");
						maidwriter.Attrib("type", "TASK_INTERVIEW"); 			    
						var maidComm = "No";
						var maidCommID = "10002";
						if (document.getElementById("SERVICE_MAGIC_maid_commercial").checked)
						{
							maidComm = "Yes";
							maidCommID = "10001";
						}
						//string[] armaidComm = new string[1];
						//armaidComm = maidComm.Split(splitter);
						maidwriter.BeginNode("answer");
						maidwriter.Attrib("id", maidCommID);
						maidwriter.Attrib("questionID", "10019");
						maidwriter.WriteCData(maidComm);
						maidwriter.EndNode();  // end answer
						maidwriter.EndNode();  // end question
				    
						//End the interview element
						maidwriter.EndNode();  // end interview
				    
						//End the task element
						maidwriter.EndNode();  // end task
				    
						// end the root element
						maidwriter.EndNode();

						//maidwriter.Flush();         
						//Write the XML to file and close the writer
						maidwriter.Close();  
						document.getElementById("SERVICE_MAGIC_maid_XML").value=(maidFile+maidwriter.ToString().replace(/</g,"\n<"));				

}//endif
}//end of makeXML