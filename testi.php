<?php
    include_once("modules/muistiinpano.php");
    include_once("modules/tietokanta.php");
    
    // LisaaMuistiinpanoTokenilla($kayttajatoken, $otsikko, $data, $vainlukuToken, $kirjoitusToken)
    if (isset($_POST['usertoken']) && isset($_POST['otsikko']) && isset($_POST['data']) && isset($_POST['lisaa']))
    {
        $token = $_POST['usertoken'];
        $otsikko = $_POST['otsikko'];
        $data = $_POST['data'];

        $tk = new Tietokanta;

        $tk->LisaaMuistiinpanoTokenilla($token, $otsikko, $data, "", "");
    }

    else if (isset($_GET['userid']))
    {
        $uid = $_GET['userid'];

        $tk = new Tietokanta;

        $muistiinpanot = $tk->HaeMuistiinpanotOmistajalta($uid);

        $teksti = $tk->KonvertoiJSONiksi($muistiinpanot);

        echo $teksti;

    }

    // sample user token 3c469e9d6c5875d37a43f353d4f88e61fcf812c66eee3457465a40b0da4153e0
    else if (isset($_GET['usertoken']))
    {
        $token = $_GET['usertoken'];

        $tk = new Tietokanta;

        $muistiinpanot = $tk->HaeMuistiinpanotOmistajanTokenilla($token);

        $teksti = $tk->KonvertoiJSONiksi($muistiinpanot);

        echo $teksti;
    }


    else if( isset($_GET['token']))
    {
        $token = $_GET['token'];

        $tk = new Tietokanta;

        $muistiinpanot = $tk->HaeMuistiinpanoTokenilla($token);

        $teksti = $tk->KonvertoiJSONiksi($muistiinpanot);

        echo $teksti;

    }

    

?>