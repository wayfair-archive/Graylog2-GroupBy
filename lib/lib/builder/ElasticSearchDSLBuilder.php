<?php // vim:set ts=4 sw=4 et:
require_once 'ElasticSearchDSLBuilderQuery.php';
require_once 'ElasticSearchDSLBuilderRangeQuery.php';

/**
 * Helper stuff for working with the ElasticSearch DSL
 * How to build a mildly complex query:
 * $dsl = new ElasticSearchDSL;
 * $bool = $dsl->bool(); // Return a new bool structure
 *
 * @author Raymond Julin <raymond.julin@gmail.com>
 * @package ElasticSearchClient
 * @since 0.1
 * Created: 2010-07-23
 */
class ElasticSearchDSLBuilder {

    protected $dsl = array();

    private $explain = null;
    private $from = null;
    private $size = null;
    private $fields = null;
    private $query = null;
    private $facets = null;
    private $sort = null;
    
    /**
     * Construct DSL object
     *
     * @return ElasticSearchDSL
     * @param array $options
     */
    public function __construct(array $options=array()) {
        foreach ($options as $key => $value)
            $this->$key = $value;
    }

    /**
     * Add array clause, can only be one
     *
     * @return ElasticSearchDSLBuilderQuery
     * @param array $options
     */
    public function query(array $options=array()) {
        if (!($this->query instanceof ElasticSearchDSLBuilderQuery))
            $this->query = new ElasticSearchDSLBuilderQuery($options);
        return $this->query;
    }

    /**
     * Build the DSL as array
     *
     * @return array
     */
    public function build() {
        $built = array();
        if ($this->from != null)
            $built['from'] = $this->from;
        if ($this->size != null)
            $built['size'] = $this->size;
        if (!$this->query)
            throw new Exception("Query must be specified");
        else
            $built['query'] = $this->query->build();
        return $built;
    }
}
