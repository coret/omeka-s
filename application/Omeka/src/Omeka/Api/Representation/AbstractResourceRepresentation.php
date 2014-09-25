<?php
namespace Omeka\Api\Representation;

use Omeka\Api\Adapter\AdapterInterface;

/**
 * Abstract API resource representation.
 *
 * Provides functionality for representations of registered API resources.
 */
abstract class AbstractResourceRepresentation extends AbstractRepresentation
{
    /**
     * The vocabulary IRI used to define Omeka application data.
     */
    const OMEKA_VOCABULARY_IRI = 'http://omeka.org/vocabulary#';

    /**
     * The JSON-LD term that expands to the vocabulary IRI.
     */
    const OMEKA_VOCABULARY_TERM = 'o';

    /**
     * @var string|int
     */
    protected $id;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var array The JSON-LD context.
     */
    protected $context = array(
        self::OMEKA_VOCABULARY_TERM => self::OMEKA_VOCABULARY_IRI,
    );

    /**
     * Get an array representation of this resource using JSON-LD notation.
     *
     * @return array
     */
    abstract public function getJsonLd();

    /**
     * Construct the resource representation object.
     *
     * @param string|int $id The unique identifier of this resource
     * @param mixed $data The data from which to derive a representation
     * @param ServiceLocatorInterface $adapter The corresponsing adapter
     */
    public function __construct($id, $data, AdapterInterface $adapter)
    {
        // Set the service locator first.
        $this->setServiceLocator($adapter->getServiceLocator());
        $this->setId($id);
        $this->setData($data);
        $this->setAdapter($adapter);
    }

    /**
     * Get the unique resource identifier.
     *
     * @return string|int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Compose the complete JSON-LD object.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $jsonLd = $this->getJsonLd();
        return array_merge(
            array(
                '@context' => $this->context,
                '@id' => $this->apiUrl(),
                'o:id' => $this->getId(),
            ),
            $jsonLd
        );
    }

    /**
     * Add a term definition to the JSON-LD context.
     *
     * @param string $term
     * @param string|array $map The IRI or an array defining the term
     */
    protected function addTermDefinitionToContext($term, $map)
    {
        $this->context[$term] = $map;
    }

    /**
     * Set the unique resource identifier.
     *
     * @param $id
     */
    protected function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set the corresponding adapter.
     *
     * @param AdapterInterface $adapter
     */
    protected function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Get the corresponding adapter or another adapter by resource name.
     *
     * @param null|string $resourceName
     * @return AdapterInterface
     */
    protected function getAdapter($resourceName = null)
    {
        if (is_string($resourceName)) {
            return parent::getAdapter($resourceName);
        }
        return $this->adapter;
    }

    public function apiUrl()
    {
        $url = $this->getServiceLocator()->get('ViewHelperManager')->get('Url');
        return $url(
            'api/default',
            array(
                'resource' => $this->getAdapter()->getResourceName(),
                'id' => $this->getId()
            ),
            array('force_canonical' => true)
        );
    }
}
