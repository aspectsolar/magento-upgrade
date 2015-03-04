jQuery(function(){
  
 
	  var   current,
	  	    next, 
	    	prev,
	   	    target, 
			hash,
			url,
			page,
			title,
			errorMessage = '<p>Sorry, an error occurred <br/> Check the path of the page you are loading or your connection</p>',
			projectIndex,
			projectLength,
			ajaxLoading = false,
			contentH,
			initialLoad = true,
			contentState =false,
			thumbContainer = jQuery('div#folio-grid'),
			contentContainer = jQuery('div#ajax-content-inner'),
			contentNavigation = jQuery('#folio-navigation ul'),
			exitProject = jQuery('div#closeProject a'),
			easeing = 'easeInOutQuint',
			folderName ='folio',
			scrollPostition;
		
		
	  // Bind an event to window.onhashchange 
	  jQuery(window).bind( 'hashchange', function() {


		//Project path
		var root = '#!'+ folderName +'/';
		var rootLength = root.length;


		//Get fragment
		hash = jQuery(window.location).attr('hash'); 
		

		//Strip #! and get it as a string - what we'll use for loading
	    url = hash.replace(/[#\!]/g, '' ); 
		

	
		//Remove class from current project
		thumbContainer.find('div.folio-thumb-container.currentProject').children().removeClass('active');
		thumbContainer.find('div.folio-thumb-container.currentProject').removeClass('currentProject' );
		
		//get menu height
					var correction = 50;
					if(isMobile==true) correction = 20;
					var headerH = jQuery('.header').outerHeight()+correction;
		 
		
		//Three scenarios
		//Project url entered in address bar - position and load
		if(initialLoad == true && hash.substr(0,rootLength) ==  root){

	

				jQuery('html,body').stop().animate({scrollTop: (contentContainer.offset().top+600)+'px'},800,'easeInOutQuint', function(){
		
						
					loadContent();
					
																									  
				});
				
		//Browsing from project to project - fade project out- delete it
		}else if(initialLoad == false && hash.substr(0,rootLength) == root){


				
					jQuery('html,body').stop().animate({scrollTop: (contentContainer.offset().top-headerH)+'px'},800,'easeInOutQuint', function(){ 
		
		
					if(contentState == false){
						
							
							loadContent();
							
					}else{
		
		
							contentContainer.animate({opacity:0,height:contentH},function(){
									
									
								loadContent();	 //load the requested
								

							 });
					}
							
							contentNavigation.fadeOut('fast');
							exitProject.fadeOut('fast');
							
					});
			
		//Going back to initial using browser back button - remove all projects.	
		}else if(hash=='' && initialLoad == false || hash.substr(0,rootLength) != root && initialLoad == false){
	
	
				jQuery('html,body').stop().animate({scrollTop: scrollPostition+'px'},1000,function(){
							
							
							unloadContent();	
							
							
				});
				

		}
		
		
		//Select current link
		 thumbContainer.find('div.folio-thumb-container .folio-thumb a[href="#!' + url + '"]' ).parent().parent().addClass( 'currentProject' );
		 thumbContainer.find('div.folio-thumb-container.currentProject').find('.folio-thumb').addClass('active');
	

		
	});
	  
	  
	  	/*load content
		---------------------------------*/
		
		function loadContent(){
			

		    //Show "loader" content while AJAX content loads.
			jQuery('div#loader' ).fadeIn('fast').removeClass('errorMessage').html('');
			
			
			if(!ajaxLoading) {
				
	            ajaxLoading = true;
				
				//Load the requested page- get section & load status 
				contentContainer.load( url +' div#ajaxpage', function(xhr, statusText, request){
																   
						if(statusText == "success"){
								
								
								ajaxLoading = false;
								
									page =  jQuery('div#ajaxpage')
			
			
									//init the necessary slider or other
									jQuery('.flexslider').flexslider({
												
												animation: "face",
												slideDirection: "horizontal",
												slideshow: true,
												slideshowSpeed: 3500,
												animationDuration: 500,
												directionNav: true,
												controlNav: true,
												
										});
			
														  
										//get container height
										contentH = contentContainer.children('div#ajaxpage').height()+'px';
										hideLoader();
											
								
						}
						
						if(statusText == "error"){
						
								jQuery('div#loader').addClass('errorMessage').append(errorMessage);
								
								jQuery('div#loader').find('p').slideDown();
								
								//alert("An error occurred: " + request.status + " - " + request.statusText);
						}
					 
					});
				
			}
			
		}
		
		/* hide loader
		---------------------------------*/
		
		function hideLoader(){
		
			//Hide loader
			jQuery('div#loader' ).fadeOut('fast', function(){
													  
					showContent();
					
			});
			 
		}	
		
		/*show content
		---------------------------------*/
		
		function showContent(){


			if(contentState==false){

					contentContainer.animate({opacity:1,height:contentH},800, function(){
				
						scrollPostition = jQuery('html,body').scrollTop();
						contentNavigation.fadeIn('fast');
						exitProject.fadeIn();
						contentState = true	
								
					});
					
			}else{

					contentContainer.animate({opacity:1,height:contentH}, function(){																		  
				
						scrollPostition = jQuery('html,body').scrollTop();
						contentNavigation.fadeIn('fast');
						exitProject.fadeIn();
						
					});
					
			}
					
			
			projectIndex = 	thumbContainer.find('div.folio-thumb-container.currentProject').index();
			projectLength = jQuery('div.folio-thumb-container .folio-thumb').length-1;
			
			
			if(projectIndex == projectLength){
				
				jQuery('ul li#nextProject a').addClass('disabled');
				jQuery('ul li#prevProject a').removeClass('disabled');
				
			}else if(projectIndex == 0){
				
				jQuery('ul li#prevProject a').addClass('disabled');
				jQuery('ul li#nextProject a').removeClass('disabled');
				
			}else{
				
				jQuery('ul li#nextProject a,ul li#prevProject a ').removeClass('disabled');
				
			}

		
	  }
	  
	  
	  /*remove content
	  ---------------------------------*/
	  
	  function unloadContent(){
	
			
			contentContainer.animate({opacity:0,height:'0px'}, function (){
							
					//remove page - switch to detach(), 
					//if data is to be kept
					jQuery(this).empty();
					contentState = false	
					
														 
			});
		
	
			contentNavigation.fadeOut();
			exitProject.fadeOut();


	  }
	  
	  
	   /*next project link
	  ---------------------------------*/
	  
	  //navigate to next project in line
	  jQuery('#nextProject a').on('click',function () {
											   							   
					 
		    current = thumbContainer.find('.folio-thumb-container.currentProject');
		    next = current.next('.folio-thumb-container');
		    target = jQuery(next).children('div').children('a').attr('href');
			jQuery(this).attr('href', target);
			
		
		  if (next.length === 0) { 
		  
		  		//reached limit
			   return false;
			  
		   } 
		   
		   current.removeClass('currentProject'); 
		   current.children().removeClass('active');
		   next.addClass('currentProject');
		   next.children().addClass('active');
		   
		  
		   
		});
	  
	   /*prev project link
	   ---------------------------------*/
	  
	    //navigate to prev project in line
	    jQuery('#prevProject a').on('click',function () {
			
			
		    current = thumbContainer.find('.folio-thumb-container.currentProject');
		    prev = current.prev('.folio-thumb-container');
			target = jQuery(prev).children('div').children('a').attr('href');
			jQuery(this).attr('href', target);
			
		   
		   if (prev.length === 0) { 

			  //reached limit
			  return false;
			
		   }
		   
		   current.removeClass('currentProject');  
		   current.children().removeClass('active');
		   prev.addClass('currentProject');
		   prev.children().addClass('active');
		   
		});
		
		
		/*close project
	    ---------------------------------*/

	  	//remove project and return to grid
		 jQuery('#closeProject a, #closeProjectMobile a').on('click',function () {

			
			//history.pushState('', document.title, window.location.pathname); // remove #name
			//window.location.hash ='#folio'
			
			unloadContent(); //remove content
			
			thumbContainer.find('div.folio-thumb-container.currentProject').children().removeClass('active');
			
			jQuery('div#loader' ).fadeOut();

			return false;
			
		});
		 

	  
		 // Since the event is only triggered when the hash changes, we need to trigger
		 // the event now, to handle the hash the page may have loaded with.
		 jQuery(window).trigger( 'hashchange' );
		 
		 //Make content reposition if browser is resized
		 jQuery(window).bind('resize',function(){
						
			jQuery(contentContainer).css({height:'auto'});	
										 
		});
		 
		 initialLoad = false;
	  
});
