function tags_after_single_post_content($content) {

//if( is_singular('post') && is_main_query() ) {
//if( is_main_query() ) {
//if (in_the_loop()){
$tags = the_tags('<br />Tags: ',' • ','<br />'); 

$content .= $tags;
//}
 //}
return $content;
}

add_filter( 'the_content', 'tags_after_single_post_content');
