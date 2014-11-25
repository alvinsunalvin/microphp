<?php

interface phpfastcache_driver {
    /*
     * Check if this Cache driver is available for server or not
     */
     function __construct($option = array());

     function checkdriver();

    /*
     * SET
     * set a obj to cache
     */
     function driver_set($keyword, $value = "", $time = 300, $option = array() );

    /*
     * GET
     * return null or value of cache
     */
     function driver_get($keyword, $option = array());

    /*
     * Delete
     * Delete a cache
     */
     function driver_delete($keyword, $option = array());

    /*
     * clean
     * Clean up whole cache
     */
     function driver_clean($option = array());
}