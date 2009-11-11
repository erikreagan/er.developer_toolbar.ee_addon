$(document).ready(function(){
    
    jQuery('.no_link').click(function(){return false;});
    
    jQuery('#er_developer_toolbar.hor').mouseenter(function(){
        jQuery('#er_developer_toolbar.hor').stop().animate({width:'90%'},0,function(){
            jQuery('#er_developer_toolbar.hor > ul').fadeIn('medium');
            jQuery('#er_developer_toolbar.hor > .divider').fadeIn('medium');
        });
    });
            
    jQuery('#er_developer_toolbar.hor').mouseleave(function(){
        jQuery(this).animate({opacity:1.0},1000,function(){
            jQuery('#er_developer_toolbar.hor > ul').hide();
            jQuery('#er_developer_toolbar.hor > .divider').hide();
            jQuery('#er_developer_toolbar.hor').animate({width:'100px'},250);
        });
    });
    
    jQuery('#er_developer_toolbar.vert').mouseenter(function(){
        jQuery('#er_developer_toolbar.vert').stop().animate({height:'90%'},0,function(){
            jQuery('#er_developer_toolbar.vert > ul').fadeIn('medium');
            jQuery('#er_developer_toolbar.vert > .divider').fadeIn('medium');
        });
    });
    
    jQuery('#er_developer_toolbar.vert').mouseleave(function(){
        jQuery(this).animate({opacity:1.0},1000,function(){
            jQuery('#er_developer_toolbar.vert > ul').hide();
            jQuery('#er_developer_toolbar.vert > .divider').hide();
            jQuery('#er_developer_toolbar.vert').animate({height:'25px'},300);
        });
    });
    
});