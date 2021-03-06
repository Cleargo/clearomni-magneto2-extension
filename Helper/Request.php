<?php


/**
 * Catalog data helper
 */
namespace Cleargo\Clearomni\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Request extends AbstractHelper
{

    protected $_objectManager;
    protected $_filesystem;


    protected $curl;
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    protected $quoteRepository;
    protected $connection;
    protected $customerSession;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @param Magento\Framework\App\Helper\Context $context
     * @param Magento\Framework\ObjectManagerInterface $objectManager
     * @param Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->_objectManager = $objectManager;
        $this->_filesystem = $filesystem;
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->timezone = $timezone;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->customerSession = $customerSession;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection();
        parent::__construct($context);
    }

    public function getBaseUrl()
    {
        return $this->scopeConfig->getValue(
            \Cleargo\Clearomni\Helper\Data::XML_BASEURL_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function request($url,$returnArray=true){
        if(empty($this->getBaseUrl())){
            return false;
        }
        $this->curl->get($this->getBaseUrl() . $url);
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/clearomni_api_'.date("Y_m_d").'.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info(json_encode(['request_url'=>$url,'requset_body'=>'empty','response_body'=>$this->curl->getBody(),'response_code'=>$this->curl->getStatus(),'date'=>date('d-m-Y H:i:s'),'debug'=>json_encode(debug_backtrace())]));
        $response = json_decode($this->curl->getBody(), $returnArray);
        return $response;
    }

}