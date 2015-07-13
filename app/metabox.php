<?php

add_action('add_meta_boxes', 'tx_add_onepager_metabox');
add_action('admin_enqueue_scripts', 'tx_onepager_metabox_scripts');

function tx_get_groups($groups){
  return implode("", array_map(function($group){return "og-".$group;}, $groups));
}

function tx_add_onepager_metabox(){
    $template = function($post){
        $onepagerLayouts = onepager()->layoutManager()->all();
        $groups = array_unique(array_reduce($onepagerLayouts, function($carry, $layout){
          return array_merge($carry, $layout['group']);
        }, []));

        //generate livemode url
        $url = League\Url\Url::createFromUrl(get_permalink($post->ID));
        $query = $url->getQuery();
        $query->modify(array('livemode' => true));

        include __DIR__ . "/views/page-meta.php";
    };

    add_meta_box(
        'onepager-metabox',
        __( 'Preset Templates', 'onepager' ),
        $template,
        'page'
    );
}

function tx_onepager_metabox_scripts($hook){
    global $post;

    if (!($post && $post->post_type == "page")) return;
    if (!($hook == 'post-new.php' || $hook == 'post.php')) return;

    //generate livemode url
    $url = League\Url\Url::createFromUrl(get_permalink($post->ID));
    $query = $url->getQuery();
    $query->modify(array('livemode' => true));

    $data = array(
        'pageId' => $post->ID,
        'livemode' => $url->__toString()
    );

    wp_enqueue_script('tx-onepager-page-meta', asset('assets/meta.js'), true);
    wp_enqueue_style( 'tx-lithium', asset( 'assets/css/lithium-builder.css' ) );

    wp_localize_script('tx-onepager-page-meta', 'onepager', $data);
}
