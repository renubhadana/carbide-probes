<form role="search" method="get" class="search-form" action="<?php echo home_url( '/' ); ?>">
    <ul>
        <li class="append field">
            <input type="search" class="xwide text input" placeholder="<?php echo ot_get_option('search_text', 'placeholder'); ?>" value="<?php echo get_search_query() ?>" name="s" />
            <button type="submit" class="medium squared primary btn"><i class="fa fa-search"></i></button>

        </li>
    </ul>
</form>
