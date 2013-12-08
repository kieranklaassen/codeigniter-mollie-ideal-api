<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

/*

grbuiken als:

    //doe betaling
    $this->load->library('mollie_ideal');
    $result = $this->mollie_ideal->pay('1000','test',9999);

    //check betaling van transaction_id
    $this->load->library('mollie_ideal');
    $result = $this->mollie_ideal->check('68c2c0a18689e5e2f4622383b8ca2039');

*/

class Mollie_ideal {

    var $partnerID = 'XXXXXX';
    var $testMode = true;
    
    function banklist()
    {
        require_once('ideal.class.php');
        
        $iDEAL = new iDEAL_Payment ($this->partnerID);
        
        if($this->testMode){
            $iDEAL->setTestMode();
        }
        
        $bank_array = $iDEAL->getBanks();
        if ($bank_array == false)
        {
            echo '<p>Er is een fout opgetreden bij het ophalen van de banklijst: ', $iDEAL->getErrorMessage(), '</p>';
            exit;
        }
        else
        {
            return $bank_array;
        }
    }
    
    function pay($amount, $description, $bank_id)
    {
          require_once('ideal.class.php');
          
          //$amount      = 118;    // Het af te rekenen bedrag in centen (!!!)
          //$description = 'Testbetaling'; // Beschrijving die consument op zijn/haar afschrift ziet.
          
          $return_url  = 'http://lichtopverlies.nl/bestel/ideal'; // URL waarnaar de consument teruggestuurd wordt na de betaling
          $report_url  = 'http://lichtopverlies.nl/bestel/ideal'; // URL die Mollie aanvraagt (op de achtergrond) na de betaling om de status naar op te sturen
          
          if (!in_array('ssl', stream_get_transports()))
          {
            echo "<h1>Foutmelding</h1>";
            echo "<p>Uw PHP installatie heeft geen SSL ondersteuning. SSL is nodig voor de communicatie met de Mollie iDEAL API.</p>";
            exit;   
          }
          
          $iDEAL = new iDEAL_Payment ($this->partnerID);
         
        if($this->testMode){
            $iDEAL->setTestMode();
        }
          
          if (isset($bank_id) and !empty($bank_id)) 
          {
            if ($iDEAL->createPayment($bank_id, $amount, $description, $return_url, $report_url)) 
            {
                /* Hier kunt u de aangemaakte betaling opslaan in uw database, bijv. met het unieke transactie_id
                   Het transactie_id kunt u aanvragen door $iDEAL->getTransactionId() te gebruiken. Hierna wordt 
                   de consument automatisch doorgestuurd naar de gekozen bank. */
                $data = array(
                    'transactionId' => $iDEAL->getTransactionId(),
                    'bankUrl' => $iDEAL->getBankURL()
                );
                return $data;
                //header("Location: " . $iDEAL->getBankURL());
                //exit; 
            }
            else 
            {
                /* Er is iets mis gegaan bij het aanmaken bij de betaling. U kunt meer informatie 
                   vinden over waarom het mis is gegaan door $iDEAL->getErrorMessage() en/of 
                   $iDEAL->getErrorCode() te gebruiken. */
                
                $data = array(
                    'error' => true,
                    'msg' => $iDEAL->getErrorMessage()
                );
                return $data;
                
            }
          }
    }
    function check($transaction_id)
    {
        require_once('ideal.class.php');
        
        $partner_id  = $this->partnerID; // Uw mollie partner ID
        
        $iDEAL = new iDEAL_Payment ($partner_id);
        if($this->testMode){
            $iDEAL->setTestMode();
        }
        
        $iDEAL->checkPayment($transaction_id);
    
        if ($iDEAL->getPaidStatus() == true) 
        {
            /* De betaling is betaald, deze informatie kan opgeslagen worden (bijv. in de database).
               Met behulp van $iDEAL->getConsumerInfo(); kunt u de consument gegevens ophalen (de 
               functie returned een array). Met behulp van $iDEAL->getAmount(); kunt u het betaalde
               bedrag vergelijken met het bedrag dat afgerekend zou moeten worden. */
            $data = array(
                'customerInfo' => $iDEAL->getConsumerInfo(),
                'amount' => $iDEAL->getAmount()
            );
            return $data;
        }
        else
        {
            $data = array(
                'error' => true,
                'msg' => 'Kon geen betaling vinden met deze transactie id.'
            );
            return $data;
        } 
        
    }
    
}