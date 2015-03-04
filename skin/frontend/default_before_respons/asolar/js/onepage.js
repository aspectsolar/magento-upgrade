/*-------------------------------------------------------------------------

	Theme Name: EGO - html - v.1
	
	For any questions concerning this theme please refer to documention or
	our forum at support.udfrance.com.

/*------------------------------------------------------------------------

//GENERAL FUNCTONS ///////////////////////////////////////////////////////

-------------------------------------------------------------------------*/

jQuery(document).ready(function(){
	
	/*vars used throughout*/
	var wh,
		scrollSpeed = 1000,
		parallaxSpeedFactor = 0.6,
		scrollEase ='easeOutExpo',
		targetSection,
		sectionLink = 'a.navigateTo',
		menuLink = jQuery('ul.navigation li a'),
	 	section = jQuery('.section'),
		toggleMenu =jQuery('.mobileMenuToggle'),
		foliothumb = jQuery('.folio-thumb'),
		thumbW,
		thumbH,
		thumbCaption,
		target,
		hoverSpeed=500,
		hoverEase='easeOutExpo';


	//INIT --------------------------------------------------------------------------------/
	
	
	if(isMobile == true){
	
		jQuery('.header').addClass('mobileHeader');	//add mobile header class
		
		//Clone header
		jQuery('.header').clone().prependTo('.page');
		
		//Set section links
		jQuery('.page').each(function() {
		 var curr_sec=jQuery(this).attr('id');
		 jQuery(this).children('.header').children('.inner').children('.navigation').children('li').each(function() {
		  if(jQuery(this).children('a').attr('href').substring(1)==curr_sec) {
		   	jQuery(this).children('a').addClass('active');
		  }
		 });
		});
	
	}else{
		
		jQuery('.page').addClass('desktop');
		jQuery('.teaser').addClass('fixed');

	}
	
	
		
	
	
			
	//PARALLAX ----------------------------------------------------------------------------/
	
	
	jQuery(window).bind('load', function() { 
								  
		parallaxInit();						  
		
	});

	function parallaxInit(){
		
		if(isMobile == true) return false;

		jQuery('#teaser-1').parallax();
		jQuery('#teaser-2').parallax();
		jQuery('#teaser-3').parallax();		
		/*add as necessary*/
		
	}
	
	
	//HOMEPAGE SPECIFIC -----------------------------------------------------------------/
	
	
	function sliderHeight(){
		
		wh = jQuery(window).height();
		jQuery('#homepage').css({height:wh});
	
	}
	
	sliderHeight();	

	var lH = jQuery('.logo-homepage').height();
	var	lW = jQuery('.logo-homepage').width();
	
	

	//PAGE SPECIFIC ---------------------------------------------------------------------/
		
	
		/*page scrolling
		-------------------*/
		
		jQuery(document).on('click', sectionLink, function(event) { 

			//kill slider timer
			jQuery.fn.epicSlider.killTimer();

			//get current
			targetSection = jQuery(this).attr('href');
			
			//Set doc title
			document.title = '';

			//prevent click if active
			//if(jQuery(this).hasClass('active')) return false;
				
			//get pos of target section
			var targetOffset = jQuery(targetSection).offset().top+1;

			//scroll			 
			jQuery('html,body').animate({scrollTop: targetOffset}, scrollSpeed, scrollEase, function() {

					//set hash
					//window.location.hash = targetSection

			 });
			
			return false;
			//event.preventDefault();
		
		});
		
		

		/*nav handling
		-------------------*/
		
		jQuery(function(){	   
				   
			if(isMobile == true) return false;	   

			section.waypoint({
						  
				handler: function(event, direction) {
				
					var activeSection = jQuery(this);
					
					if (direction === "up") {
						
						activeSection = activeSection.prev();
						
					}

					var activeMenuLink = jQuery('ul.navigation li a[href=#' + activeSection.attr('id') + ']');
					
					menuLink.removeClass('active');
					activeMenuLink.addClass('active');
					
					
					//position header for mobile - solves fixed position issue
					var targetOffset = jQuery(activeSection).offset().top;
					if(isMobile == true && jQuery(window).scrollTop() >= wh) jQuery('.header').css({top:targetOffset,display:'none'}).fadeIn();
					
		
				},
				
				offset: '35%'			//when it should switch on consecutive page
				
			});
	
		});
	
		/*nav reveal
		-------------------*/

		 jQuery(window).bind('scroll', function(){
			
			
			   
		}); 
	 
	 
		function headerReveal(){
			
			 if(isMobile == true) return false;	

			if (jQuery(window).scrollTop() >= wh){
	 
					if(!jQuery('.header').is(':animated')) {
						
						jQuery('.header').stop(true,true).slideDown();
						/*push elements out of view when scrolling*/
							  
					}
			}else{
					if(!jQuery('.header').is(':animated')) {	
					
						jQuery('.header').stop(true,true).slideUp();
						jQuery('ul.navigation').fadeOut();
						toggleMenu.removeClass('open');
						
						
					}
								  
			} 
	
		 }

		
	//ROLLOVER SPECIFIC ---------------------------------------------------------------------/
		
	
		/*folio
		-------------------*/
		
		foliothumb.on({

		 mouseenter: function () {
	
	 
			 //check if device is mobile 
			 //or within an inactive filter category
			 //or if its video content in which case do nothing
			 if(isMobile == true) return false;
			 
			 thumbW = foliothumb.find('a').find('img').width();
			 thumbH = foliothumb.find('a').find('img').height();
			 
			//get refrences needed
		 	thumbCaption = jQuery(this).find('a').attr('title');
			
			//add rolloverscreen
			if(!jQuery(this).find('a').find('div').hasClass('folio-thumb-rollover')) jQuery(this).find('a').append('<div class="folio-thumb-rollover"></div>');
			
			
			//set it to the image size and fade in
			hoverScreen = jQuery('.folio-thumb-rollover')
			hoverScreen.css({width:thumbW,height:thumbH});

			
			//make sure caption is filled out
			if (typeof thumbCaption !== 'undefined' && thumbCaption !== false && jQuery(this).find(hoverScreen).is(':empty')) {
				
				//construct rollover & animate
   				jQuery(this).find(hoverScreen).append('<div class="thumbInfo">'+thumbCaption+'</div>');
				target = jQuery(this).find(hoverScreen);
				target.stop().animate({opacity:1},hoverSpeed, hoverEase);
			}
			
		}, 
		
		  mouseleave: function () {
		
			if(isMobile == true) return false;
			
			//animate out
			jQuery(this).find(hoverScreen).animate({opacity:0},hoverSpeed,'linear',function(){
	
					//delete rollover
				   jQuery(this).remove();
				
			});
			
		
		 }
	
	  });
		
	
	
		
	 //WINDOW EVENTS ---------------------------------------------------------------------/	
	 
	 
	 jQuery(window).bind('resize',function() {
		
		//Update slider height
	 	sliderHeight();
		
	});	
		
	
					
		

});