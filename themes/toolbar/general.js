function tb_hor() {
    jQuery('.erdtb_hor #er_developer_toolbar').mouseenter(function(){
        jQuery('.erdtb_hor').stop().animate({width:'90%'},0,function(){
            jQuery('#er_developer_toolbar > ul').fadeIn('medium');
            jQuery('#er_developer_toolbar > .divider').fadeIn('medium');
        });
    });
            
    jQuery('.erdtb_hor').mouseleave(function(){
        jQuery(this).animate({opacity:1.0},1000,function(){
            jQuery('#er_developer_toolbar > ul').hide();
            jQuery('#er_developer_toolbar > .divider').hide();
            jQuery('.erdtb_hor').animate({width:'100px'},250);
        });
    });
}

function tb_vert() {
    jQuery('.erdtb_vert #er_developer_toolbar').mouseenter(function(){
        jQuery('.erdtb_vert').stop().animate({height:'90%'},0,function(){
            jQuery('#er_developer_toolbar > ul').fadeIn('medium');
            jQuery('#er_developer_toolbar > .divider').fadeIn('medium');
        });
    });
    
    jQuery('.erdtb_vert').mouseleave(function(){
        jQuery(this).animate({opacity:1.0},1000,function(){
            jQuery('#er_developer_toolbar > ul').hide();
            jQuery('#er_developer_toolbar > .divider').hide();
            jQuery('.erdtb_vert').animate({height:'25px'},300);
        });
    });
}




jQuery(document).ready(function(){
    
    tb_hor();
    tb_vert();
    
    jQuery('.no_link').click(function(){return false;});
        
    jQuery('#move_top').click(function(){
        var container = jQuery(this).parent().parent().parent().parent().parent().parent().parent();
        jQuery("#erdtb_top").removeClass('inactive');
        jQuery('#er_developer_toolbar').clone().prependTo('#erdtb_top');
        jQuery(container).empty().addClass('inactive');
        tb_hor();
        return false;
    });
    jQuery('#move_bot').click(function(){
        var container = jQuery(this).parent().parent().parent().parent().parent().parent().parent();
        jQuery("#erdtb_bot").removeClass('inactive');
        jQuery('#er_developer_toolbar').clone().prependTo('#erdtb_bot');
        jQuery(container).empty().addClass('inactive');
        tb_hor();
        return false;
    });
    jQuery('#move_right').click(function(){
        jQuery('#er_developer_toolbar').attr('class','right vert');
        return false;
    });
    jQuery('#move_left').click(function(){
        jQuery('#er_developer_toolbar').attr('class','left vert').css({width:'auto',height:'25px'});
        jQuery('#er_developer_toolbar > ul').hide();
        return false;
    });
    
    
    
});
