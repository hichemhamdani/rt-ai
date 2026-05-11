<?php

class HandLContactForm7Tag {

    private $tag;
    private $title;

    public function __construct($tag, $title)
    {
        $this->tag = $tag;
        $this->title = $title;

        add_action( 'wpcf7_init', array($this,'wpcf7_add_form_tag_handl'), 10, 0 );
        add_action( 'wpcf7_admin_init', array($this, 'wpcf7_add_tag_generator_handl'), 999, 0 );
     }

    public function wpcf7_add_form_tag_handl(){
        wpcf7_add_form_tag($this->tag, array($this, 'wpcf7_handl_form_tag_handler'), array( 'name-attr' => true ));
    }

    public function wpcf7_handl_form_tag_handler($tag){
        $class = wpcf7_form_controls_class( $tag->type );
        $atts = array();

        $atts['class'] = $tag->get_class_option( $class );
        $atts['id'] = $tag->get_id_option();
        $atts['name'] = $tag->name;
        $atts['tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );

        $value = isset( $tag->values[0] ) ? $tag->values[0] : '';

        if ( empty( $value ) && isset($_COOKIE[$this->title]) && $_COOKIE[$this->title] != "" ) {
            $value = $_COOKIE[$this->title];
        }

        $atts['type'] = 'hidden';
        $atts['value'] = $value;

        $atts = wpcf7_format_atts( $atts );

        $html = sprintf( '<input %1$s />', $atts );

        return $html;
    }

    private function get_cf7_version() {
        
        if (defined('WPCF7_VERSION')) {
            return WPCF7_VERSION;
        }
        
        if (function_exists('get_plugin_data')) {
            $plugin_file = WP_PLUGIN_DIR . '/contact-form-7/wp-contact-form-7.php';
            if (file_exists($plugin_file)) {
                $plugin_data = get_plugin_data($plugin_file);
                return $plugin_data['Version'] ?? '5.9.0'; //fallback
            }
        }
        
        return '5.9.0'; // fallback to version 1
    }

    public function wpcf7_add_tag_generator_handl() {
        $tag_generator = WPCF7_TagGenerator::get_instance();
        
        $cf7_version = $this->get_cf7_version();
        $supports_v2 = version_compare($cf7_version, '6.0', '>=');
        
        if ($supports_v2) {
            // Use version 2 for CF7 6.0+
            $tag_generator->add( 
                $this->tag, 
                $this->title,
                array($this, 'wpcf7_tag_generator_handl'),
                array('version' => 2)
            );
        } else {
            // Fallback to version 1 for older CF7 versions
            $tag_generator->add( 
                $this->tag, 
                $this->title,
                array($this, 'wpcf7_tag_generator_handl')
            );
        }
    }

    public function wpcf7_tag_generator_handl($cf, $args){
        $args = wp_parse_args( $args, array() );
        
        // Check CF7 version for backward compatibility
        $cf7_version = $this->get_cf7_version();
        $supports_v2 = version_compare($cf7_version, '6.0', '>=');
        
        if ($supports_v2) {
            $this->render_v2_tag_generator($args);
        } else {
            $this->render_v1_tag_generator($args);
        }
    }

    private function render_v2_tag_generator($args) {
        ?>
        <header class="description-box">
            <h3><?php echo esc_html($this->title); ?> form tag generator</h3>
            <p>Generate a hidden field for <?php echo esc_html($this->title); ?> tracking.</p>
        </header>
        
        <div class="control-box">
            
            <fieldset>
                <legend>Field type</legend>
                <select data-tag-part="basetype">
                    <option value="<?php echo esc_attr($this->tag); ?>">Hidden Field</option>
                </select>
            </fieldset>
            
            <fieldset>
                <legend><?php echo esc_html( __( 'Label', 'contact-form-7' ) ); ?></legend>
                <input type="text" data-tag-part="value" class="oneline" />
            </fieldset>

            <fieldset>
                <legend><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?></legend>
                <input type="text" data-tag-part="name" class="tg-name oneline" pattern="[A-Za-z][A-Za-z0-9_\-]*" />
            </fieldset>

            <fieldset>
                <legend><?php echo esc_html( __( 'Id attribute', 'contact-form-7' ) ); ?></legend>
                <input type="text" data-tag-part="option" data-tag-option="id:" class="idvalue oneline" pattern="[A-Za-z0-9_\-]*" />
            </fieldset>

            <fieldset>
                <legend><?php echo esc_html( __( 'Class attribute', 'contact-form-7' ) ); ?></legend>
                <input type="text" data-tag-part="option" data-tag-option="class:" class="classvalue oneline" pattern="[A-Za-z0-9_\-\s]*" />
            </fieldset>

        </div>

        <footer class="insert-box">
            <div class="flex-container">
                <input type="text" class="code" readonly="readonly" onfocus="this.select();" 
                       data-tag-part="tag" aria-label="The form-tag to be inserted into the form template" />
                <button type="button" class="button button-primary" data-taggen="insert-tag">Insert Tag</button>
            </div>
            <p class="mail-tag-tip">To use the user input in the email, insert the corresponding mail-tag <strong data-tag-part="mail-tag"></strong> into the email template.</p>
        </footer>
        <?php
    }

    private function render_v1_tag_generator($args) {
        ?>
        <div class="control-box">
            <fieldset>

                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php echo esc_html( __( 'Label', 'contact-form-7' ) ); ?></label></th>
                        <td><input type="text" name="values" class="oneline" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>" /></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?></label></th>
                        <td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'contact-form-7' ) ); ?></label></th>
                        <td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'contact-form-7' ) ); ?></label></th>
                        <td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
                    </tr>

                    </tbody>
                </table>
            </fieldset>
        </div>

        <div class="insert-box">
            <input type="text" name="<?php print $this->tag; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

            <div class="submitbox">
                <input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
            </div>

            <br class="clear" />

            <p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'contact-form-7' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" /></label></p>
        </div>


        <?php
    }
}

function createContactForm7Fields(){
//    TODO: We'll implement auto addition of all the fields into both form and mail template later on.
//    $posts = WPCF7_ContactForm::find( array(
//        'post_status' => 'any',
//        'posts_per_page' => -1,
//    ) );
//
//    foreach ( $posts as $post ) {
//        /** @var WPCF7_ContactForm $post */
//        $props = $post->get_properties();
//        $props['form'] = $props['form']."\nHaktan1";
//        $props['mail']['body'] = $props['mail']['body']."\nHaktan2";
//        $post->set_properties($props);
////        $post->save();
////        dd($post->get_properties());
//    }

    $fields = generateUTMFields();
    foreach ($fields as $field){
        new HandLContactForm7Tag($field."_cf7", $field);
    }
}
add_action( 'wpcf7_init','createContactForm7Fields',9);

