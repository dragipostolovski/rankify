<?php

namespace projectsengine;

class Rankify {
    /**
     * @var array|false|string
     */
    private $plugin_domain;

    /**
     * @var array|false|string
     */
    private $version;

    /**
     * @since    1.0.0
     * @access   private
     * @var array|false|string
     */
    private $plugin_name;

    /**
     *
     */
    public function __construct() {
        $this->plugin_domain    = 'rankify';
        $this->version          = '1.0.1';
        $this->plugin_name      = 'Rankify';

        if ( is_admin() ) {
			add_action( 'load-post.php',     array( $this, 'meta_boxes' ) );
			add_action( 'load-post-new.php', array( $this, 'meta_boxes' ) );
		}

        $this->run();
    }

    /**
     * Add and save meta box hooks.
     *
	 * @return void
	 */
	public function meta_boxes() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ), 10, 1 );
	}

    /**
     * Add meta box.
     *
	 * @param $post_type
     * @param $post
	 *
	 */
	public function add_meta_box( $post_type ) {
		// Limit meta box to certain post types.

		if ( 'post' === $post_type) {
			add_meta_box(
				'voting_details',
				__( 'Voting details', $this->plugin_domain ),
				array( $this, 'render_voting_meta_box_content' ),
				$post_type,
				'advanced',
				'high',
				array( 'class' => 'voting-details' )
			);
		}
	}

    /**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_voting_meta_box_content( $post, $meta_box ) {
		$class = $meta_box['args']['class'];

		// Add a nonce field, so we can check for it later.
		wp_nonce_field( 'voting_details_box', 'voting_details_box_nonce' );

		// Use get_post_meta to retrieve an existing value from the database.
        $yes = get_post_meta( $post->ID, '_helpful_yes', true ) ?: 0;
        $no = get_post_meta( $post->ID, '_helpful_no', true ) ?: 0;

        $totalVotes = intval( $yes ) + intval( $no );

        $totalYes = ( 0 !== $totalVotes ) ? intval($yes / $totalVotes * 100 ) : 0;
        $totalNo = ( 0 !== $totalVotes ) ? intval( $no / $totalVotes * 100 ) : 0;

		// Display the form, using the current value.
		?>

		<div class="c-rankify">
            <div class="c-rankify__inner <?php echo $class; ?>">
                <div>Yes: <?php echo  $totalYes .'%'; ?></div>
                <div>No: <?php echo  $totalNo .'%'; ?></div>
            </div>
		</div>

		<?php
	}

    /**
     * Run everything.
     */
    public function run() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_filter( 'the_content', array( $this, 'add_voting_content' ) );

        add_action( 'wp_ajax_rankify_ajax', array( $this, 'rankify_ajax' ) );
        add_action( 'wp_ajax_nopriv_rankify_ajax', array( $this, 'rankify_ajax' ) );
    }

    /**
     * Public scripts.
     *
     * @return void
     */
    public function enqueue_scripts() {
        wp_enqueue_style( $this->plugin_domain . '-style', plugins_url('../assets/css/public.css', __FILE__), array() );
        wp_enqueue_script( $this->plugin_domain . '-script', plugins_url( '../assets/js/public.js', __FILE__ ), array(),  $this->version, array( 'strategy' => 'defer', 'in_footer' => false ) );
        
        wp_localize_script( $this->plugin_domain . '-script', $this->plugin_domain, array(
            'ajaxurl' 		=> admin_url('admin-ajax.php'), 
			'ajaxnonce' 	=> wp_create_nonce( 'ajaxsubmit' ),
            'post'          => get_queried_object_id()
        ) );
    }

    /**
     * Admin scripts and styles.
     *
     * @return void
     */
    public function admin_enqueue_scripts() {
        wp_enqueue_style( $this->plugin_domain . 'admin-style', plugins_url('../assets/css/admin.css', __FILE__), array() );
        wp_enqueue_script( $this->plugin_domain . 'admin-script', plugins_url( '../assets/js/admin.js', __FILE__ ), array(),  $this->version, array( 'strategy' => 'defer', 'in_footer' => false ) );
    }

    /**
     * Add the voting form at the end of the post content.
     *
     * @param sting $content
     * 
     * @return html
     */
    public function add_voting_content( $content ) {
        $form = '';
        $postId =  get_the_ID();
        $yesVotes = get_post_meta( $postId, '_helpful_yes', true ) ?: 0;
        $noVotes = get_post_meta( $postId, '_helpful_no', true ) ?: 0;
        $totalVotes = intval( $yesVotes ) + intval( $noVotes ); // total votes
        $totalYes = ( 0 !== $totalVotes ) ? intval($yesVotes / $totalVotes * 100 ) : 0;
        $totalNo = ( 0 !== $totalVotes ) ? intval( $noVotes / $totalVotes * 100 ) : 0;

        if( is_singular('post') ) {
            ob_start(); ?>

            <div class="c-rankify">
                <div class="c-rankify__inner">
                    <div class="c-rankify__question">Was this article helpful?</div>
                    <div class="c-rankify__answers">
                        <button type="button" data-answer="yes">Yes (<?php echo $totalYes,'%'; ?>)</button>
                        <button type="button" data-answer="no">No (<?php echo $totalNo,'%'; ?>)</button>
                    </div>
                </div>
            </div>

            <?php

            $form = ob_get_clean();
        }

        return $content . $form;
    }

    /**
     * What happens when the user clicks yes or no.
     */
    public function rankify_ajax() {
        $secure = check_ajax_referer( 'ajaxsubmit', 'ajaxnonce' );

        if( !$secure ) {
            wp_send_json( array(
                'response'  => false,
                'code'      => 'error',
            ) );
        }
    
        $function = $_REQUEST['function'];
    
        $answer = call_user_func( array( $this, $function ), $_REQUEST );
    
        wp_send_json( $answer );
    
        wp_die();
    }


    /**
     * Update the vote and return the new results.
     *
     * @param array $request
     * 
     * @return array
     */
    public function rankify_vote( $request ) {
        $id = $request['post'];
        $vote = $request['vote'];
        $helpful = get_post_meta( $id, '_helpful_' . $vote, true );

        ( $helpful ) ? update_post_meta( $id, '_helpful_' . $vote, ++$helpful ) : add_post_meta( $id, '_helpful_' . $vote, 1 );

        $yes = get_post_meta( $id, '_helpful_yes', true ) ?: 0;
        $no = get_post_meta( $id, '_helpful_no', true ) ?: 0;
        $totalVotes = intval( $yes ) + intval( $no );
        $totalYes = ( 0 !== $totalVotes ) ? intval($yes / $totalVotes * 100 ) : 0;
        $totalNo = ( 0 !== $totalVotes ) ? intval( $no / $totalVotes * 100 ) : 0;

        return array(
            'response' 	=> true, 
            'code'      => 'success',
            'message' 	=> __( 'Thank you for your feedback.', $this->plugin_domain ),
            'yes'       => $totalYes,
            'no'        => $totalNo,
            'class' 	=> 'alert-error',
        );
    }
}