<?php

namespace CAC\TAPoR;

/**
 * API client class.
 *
 * @since 1.0.0
 */
class Client {
	/**
	 * Base URI for the API
	 *
	 * @var string
	 * @since 1.0.0
	 */
	public $api_base = TAPOR_ENDPOINT_URL;

	/**
	 * Endpoint URL chunk.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $endpoint;

	/**
	 * Query vars.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $query_vars = array();

	/**
	 * Status code.
	 *
	 * @var int
	 * @since 1.0.0
	 */
	protected $status_code;

	/**
	 * Parsed API response.
	 *
	 * @var mixed
	 * @since 1.0.0
	 */
	protected $parsed_response;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->query_vars = array(
			/*
			'page'	    => 0,
			'pagesize'  => 100,
			'direction' => 'ASC',
			*/
		);
	}

	/**
	 * Set the endpoint (chunk of URL after the API base
	 *
	 * @since 1.0.0
	 *
	 * @param string $endpoint Endpoint URI.
	 * @return TAPoR_Client
	 */
	public function set_endpoint( $endpoint ) {
		$this->endpoint = $endpoint;
		return $this;
	}

	/**
	 * Add a query var.
	 *
	 * @param string $key   Key for the query var.
	 * @param mixed  $value Value for the query var.
	 * @return TAPoR_Client
	 */
	public function add_query_var( $key, $value ) {
		// Will overwrite existing
		$this->query_vars[ $key ] = $value;
		return $this;
	}

	/**
	 * Build the request URI out of the params.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_request_uri() {
		$request_uri = trailingslashit( $this->api_base ) . $this->endpoint;
		$request_uri = add_query_arg( $this->query_vars, $request_uri );
		return $request_uri;
	}

	/**
	 * Perform an API request.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function request() {
		$uri = $this->get_request_uri();

		$response = wp_remote_get( $uri, array(
			'timeout' => 30,
		) );
		$response_body = wp_remote_retrieve_body( $response );
		return json_decode( $response_body );
	}

	/** Specific fetchers ************************************************/

	/**
	 * Get a list of taxonomies.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function get_attribute_types() {
		return $this->set_endpoint( 'attribute_types' )->request();
	}

	/**
	 * Get a list of terms for a given taxonomy.
	 *
	 * @since 1.0.0
	 *
	 * @param int $taxonomy_id ID of the taxonomy.
	 * @return mixed
	 */
	public function get_taxonomy_terms( $taxonomy_id ) {
		return $this->set_endpoint( 'entity_taxonomy_term.json' )->add_query_var( 'parameters[vid]', intval( $taxonomy_id ) )->request();
	}

	/**
	 * Get an item by TAPoR tool ID.
	 *
	 * @since 1.0.0
	 *
	 * @param int $id ID of the tool on TAPoR.
	 * @return mixed
	 */
	public function get_item_by_id( $id ) {
		return $this->set_endpoint( 'tools/' . $id )->request();
	}

	/**
	 * Get a list of the items (nodes/tools) that match a given taxonomy term.
	 *
	 * @todo Is broken
	 *
	 * @since 1.0.0
	 *
	 * @param int $taxonomy_term_id Term ID.
	 * @return mixed
	 */
	public function get_items_for_taxonomy_term( $taxonomy_term_id ) {
		return $this->set_endpoint( 'entity_node.json' )->add_query_var( 'parameters[tid]', intval( $taxonomy_term_id ) )->request();
	}

	/**
	 * Get a list of the items (nodes/tools) that match a given category.
	 *
	 * @since 1.0.0
	 *
	 * @param int $category_id ID of the category.
	 * @return mixed
	 */
	public function get_items_for_category( $category_id ) {
		return $this->set_endpoint( 'entity_node.json' )->add_query_var( 'parameters[field_categories]', intval( $category_id ) )->request();
	}

	/**
	 * Get a list of the items (nodes/tools) that match a given TaDiRAH term.
	 *
	 * @since 1.0.0
	 *
	 * @param int $category_id TaDiRAH category ID.
	 * @return mixed
	 */
	public function get_items_for_tadirah_term( $category_id ) {
		return $this->set_endpoint( 'tools' )->add_query_var( 'attribute_values', $category_id )->request();
	}

	/**
	 * Get a list of tools that match a search term.
	 *
	 * @since 1.0.0
	 *
	 * @param string $search_term Search term.
	 * @return mixed
	 */
	public function get_items_by_search_term( $search_term ) {
		return $this->set_endpoint( 'search_node/retrieve.json' )->add_query_var( 'keys', $search_term )->request();
	}
}
