<?php
namespace basteyy\UrlSchemeValidator;

use basteyy\UrlSchemeValidator\Exceptions\RuntimeException;

class UrlSchemeValidator
{
    /**
     * @var null Array for all the passed urls
     */
    private $urls = null;

    /**
     * @var array The default set of data for a url
     */
    private $urlsDataDefaults = [
        'checked' => false,
        'scheme' => null,
        'original' => null,
        'modified' => null
    ];

    /**
     * @var string The default scheme if self::$forcedDefaultScheme is true and the url has no scheme
     */
    private $defaultScheme = 'http';

    /**
     * @var bool Force the defaultscheme self::$forceDefaultScheme
     */
    private $forceDefaultScheme = false;

    /**
     * @var array Port to scheme data
     */
    private $defaultPortToSchemeMap = [
        80 => 'http',
        8080 => 'http',
        443 => 'https',
        21 => 'ftp',
        20 => 'ftp'
    ];

    /**
     * @var array Maps ports to schemes
     */
    private $portToSchemeMap = [];

    /**
     * @var string If the url shows no scheme and the optopns $forceDefaultScheme is false, than this string will
     * shown as the scheme
     */
    private static $unknownSchemeName = 'UNKNOWN';

    /**
     * UrlSchemeValidator constructor.
     * @param null $url string
     * @todo Remove the hardcoded loading of the PortSchemeDatabase?
     */
    public function __construct($url = null)
    {
        if (null !== $url) {
            $this->setUrl($url);
        }

        // Try to load the default ressource for port to scheme mapping
        if(is_file(dirname(__DIR__) . '/src/Ressources/PortSchemeDatabase.php') ) {
            $this->portToSchemeMap = include dirname(__DIR__) . '/src/Ressources/PortSchemeDatabase.php';
        } else {
            $this->portToSchemeMap = $this->defaultPortToSchemeMap;
        }

    }

    /**
     * Overwrite the default/current port-to-scheme-data
     * @param array $data Port to scheme array (for example [80 => 'http', 443 => 'https'])
     */
    public function setSchemeData(array $data)
    {
        $this->portToSchemeMap = $data;
    }

    /**
     * Adds a new port to scheme to the current scope.
     *
     * @param $port int Port as int
     * @param $scheme string Scheme as a string
     */
    public function addPortToScheme($port, $scheme)
    {
        $this->portToSchemeMap[$port] = $scheme;
    }

    /**
     * Add a url to the scope
     * @param $url
     */
    public function setUrl($url)
    {
        $this->urls[$url] = $this->urlsDataDefaults;
        $this->urls[$url]['original'] = $url;
    }

    /**
     * Validate all urls in the scope
     */
    public function validateAll()
    {
        foreach ($this->urls as $url) {
            $this->validate($url);
        }
    }

    /**
     * Set a new default scheme
     * @param $scheme string The new default scheme
     */
    public function setDefaultScheme($scheme)
    {
        $this->defaultScheme = $scheme;
    }

    /**
     * @param bool $state State of forcing the default scheme
     */
    public function forceDefaultScheme(bool $state = true)
    {
        $this->forceDefaultScheme = $state;
    }

    /**
     * Returns the scheme for $url as a string
     * @param $url string The scheme
     */
    public function getScheme($url)
    {
        if (!$url) {
            if (!$this->urls) {
                throw new RuntimeException('A url is missing');
            }

            $url = end($this->urls)['original'];
        }

        if (!isset($this->urls[$url])) {
            $this->setUrl($url);
        }

        if (!$this->urls[$url]['checked']) {
            $this->validate($url);
        }

        return $this->urls[$url]['scheme'];
    }

    /**
     * Returns all urls from the scope
     * @return null
     */
    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * Returns the url including a scheme
     * @param null $url
     * @return string url including the scheme
     * @throws RuntimeException
     */
    public function getUrl($url = null)
    {
        if (!$url) {
            if (!$this->urls) {
                throw new RuntimeException('A url is missing');
            }

            $url = end($this->urls)['original'];
        }

        if (!isset($this->urls[$url])) {
            $this->setUrl($url);
        }

        if (!$this->urls[$url]['checked']) {
            $this->validate($url);
        }

        return $this->urls[$url]['modified'];
    }

    /**
     * Check if the scheme a webscheme (http or https)
     * @param null $url string Url which should be checked. If no domain provided, script will check the last url in
     * the scope
     * @return bool
     */
    public function isWebScheme($url = null)
    {
        if (!$url) {
            if (!$this->urls) {
                throw new RuntimeException('A url is missing');
            }

            $url = end($this->urls)['original'];
        }

        if (!isset($this->urls[$url])) {
            $this->setUrl($url);
        }

        if (!$this->urls[$url]['checked']) {
            $this->validate($url);
        }

        return 'http' == $this->urls[$url]['scheme'] || 'https' == $this->urls[$url]['scheme'] ? true : false;
    }

    /**
     * Validate $url
     * @param $url
     * @todo Throw an exception on a unknown scheme?
     */
    private function validate($url)
    {
        if (!isset($this->urls[$url])) {
            $this->setUrl($url);
        }

        $genericScheme = parse_url($url, PHP_URL_SCHEME);

        if (!$genericScheme) {
            $port = parse_url($url, PHP_URL_PORT);
            $genericScheme = isset($this->portToSchemeMap[$port]) ? $this->portToSchemeMap[$port] : self::$unknownSchemeName;
        }

        $this->urls[$url] = [
            'checked' => true,
            'scheme' => $genericScheme,
            'original' => $url,
            'modified' => $url
        ];

        // create a modifies version of the url
        if ('//' == substr($url, 0, 2) ) {

            if(self::$unknownSchemeName != $genericScheme) {

                $this->urls[$url] = [
                    'modified' => $genericScheme .':' . $url
                ];

            } elseif($this->forceDefaultScheme){

                $this->urls[$url] = [
                    'modified' => $this->defaultScheme .':' . $url
                ];
            }
        }

    }

}
