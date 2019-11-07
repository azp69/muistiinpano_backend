<?php
    class Tietokanta
    {
        public function __construct()
        {
            include("db_connection.php");
            $this->db_servername = $db_servername;
            $this->db_username = $db_username;
            $this->db_password = $db_password;
            $this->db_name = $db_name;
        }

        public function __deconstruct()
        {

        }

        public function KonvertoiJSONiksi($jsonArray)
        {
            $teksti = "";

            $teksti = $teksti . "{\n\r\"muistiinpanot\" : [\n\r";

            foreach ($jsonArray as $muistiinpano)
            {
                 $teksti = $teksti . json_encode($muistiinpano, JSON_UNESCAPED_UNICODE);
                $teksti = $teksti . ",\n\r";
            }
            $teksti = rtrim($teksti, ",\n\r");
        
            $teksti = $teksti . "\n\r]\n\r}";
            return $teksti;
        }

        public function TarkastaKayttajaTokenJaPalautaKayttajaID($token)
        {
            $connection = new mysqli($this->db_servername, $this->db_username, $this->db_password, $this->db_name);

            if ($connection->connect_error)
            {
                die("Ei saada yhteyttä tietokantaan.");
            }

            $query = "SELECT id FROM kayttaja WHERE token = '$token' LIMIT 1";

            $result = $connection->query($query);

            $omistaja_id = "";

            if ($result->num_rows > 0)
            {
                while($row = $result->fetch_assoc()) {
                    $omistaja_id = $row["id"];
                    $connection->close();
                    return $omistaja_id;
                }
            }
            else
            {
                $connection->close();
                die("Väärä token");
            }

            $connection->close();
            
        }

        public function PoistaMuistiinpanoTokenilla($kayttajatoken, $muistiinpanoID)
        {
                $this->TarkastaKayttajaTokenJaPalautaKayttajaID($kayttajatoken);

                $connection = new mysqli($this->db_servername, $this->db_username, $this->db_password, $this->db_name);
    
                if ($connection->connect_error)
                {
                    die("Ei saada yhteyttä tietokantaan.");
                }
    
                $query = "DELETE FROM muistiinpano WHERE id='$muistiinpanoID'";
    
                if ($connection->query($query) === TRUE)
                {
                    echo "DELETE OK";
                }
                else
                {
                    echo "Virhe poistettaessa muistiinpanoa:";
                    echo mysqli_error($connection);
                }
                $connection->close();

        }

        public function HaeMuistiinpanoIDlla($muistiinpanoID)
        {   // id, omistaja, otsikko, data, lisatty, vainlukuToken, kirjoitusToken
            // palauttaa muistiinpano-arrayn
            $connection = new mysqli($this->db_servername, $this->db_username, $this->db_password, $this->db_name);

            if ($connection->connect_error)
            {
                die("Ei saada yhteyttä tietokantaan.");
            }
            // $query = "SELECT muistiinpano.*, muistiinpanotoken.token FROM muistiinpano INNER JOIN muistiinpano.id = muistiinpanotoken.muistiinpanoid WHERE muistiinpano.id = '$muistiinpanoID'";
            $query = "SELECT muistiinpano.* , muistiinpanotoken.token FROM muistiinpano INNER JOIN kayttaja ON muistiinpano.omistaja = kayttaja.id LEFT JOIN muistiinpanotoken ON muistiinpano.id = muistiinpanotoken.muistiinpanoid WHERE muistiinpano.id = '$muistiinpanoID'";

            $result = $connection->query($query);
            
            $muistiinpanot = array();
            $i = 0;

            if ($result->num_rows > 0) 
            {
                while($row = $result->fetch_assoc()) {
                    $id = $row["id"];
                    $omistaja = $row["omistaja"];
                    $otsikko = $row["otsikko"];
                    $data = $row["data"];
                    $lisatty = $row["lisatty"];
                    $token = $row["muistiinpano.token"];
                    
                    $muistiinpano = new Muistiinpano($id, $omistaja, $otsikko, $data, $lisatty, $token);

                    $muistiinpanot[$i++] = $muistiinpano;
                }
            }
            
            $connection->close();

            return $muistiinpanot;

        }

        public function PaivitaMuistiinpanoTokenilla($id, $kayttajatoken, $otsikko, $data, $token)
        {

            $omistaja_id = $this->TarkastaKayttajaTokenJaPalautaKayttajaID($kayttajatoken);
            
            $connection = new mysqli($this->db_servername, $this->db_username, $this->db_password, $this->db_name);

            if ($connection->connect_error)
            {
                die("Ei saada yhteyttä tietokantaan.");
            }
            
            $query = "UPDATE muistiinpano SET otsikko='$otsikko', data='$data' WHERE id='$id'";

            if ($connection->query($query) === TRUE)
            {
                // echo "OK";
            }
            else
            {
                echo "Virhe lisätessä muistiinpanoa:";
                echo mysqli_error($connection);
            }

            $lastid = $id;
            
            $query = "SELECT muistiinpano.* , muistiinpanotoken.token FROM muistiinpano INNER JOIN kayttaja ON muistiinpano.omistaja = kayttaja.id LEFT JOIN muistiinpanotoken ON muistiinpano.id = muistiinpanotoken.muistiinpanoid WHERE muistiinpano.id = '$lastid'";

            $result = $connection->query($query);
            
            $muistiinpanot = array();
            $i = 0;

            if ($result->num_rows > 0) 
            {
                while($row = $result->fetch_assoc()) {
                    $id = $row["id"];
                    $omistaja = $row["omistaja"];
                    $otsikko = $row["otsikko"];
                    $data = $row["data"];
                    $lisatty = $row["lisatty"];
                    $token = $row["token"];
                    
                    $muistiinpano = new Muistiinpano($id, $omistaja, $otsikko, $data, $lisatty, $token);

                    $muistiinpanot[$i++] = $muistiinpano;
                }
            }
            

            $connection->close();
            return $muistiinpanot;
        }

        
        public function LisaaMuistiinpanoTokenilla($kayttajatoken, $otsikko, $data, $token)
        {

            $omistaja_id = $this->TarkastaKayttajaTokenJaPalautaKayttajaID($kayttajatoken);
            
            $connection = new mysqli($this->db_servername, $this->db_username, $this->db_password, $this->db_name);

            if ($connection->connect_error)
            {
                die("Ei saada yhteyttä tietokantaan.");
            }
            
            $query = "INSERT INTO muistiinpano (omistaja, otsikko, data) VALUES ('$omistaja_id', '$otsikko', '$data')";

            if ($connection->query($query) === TRUE)
            {
                // echo "OK";
            }
            else
            {
                // echo "Virhe lisätessä muistiinpanoa:";
                // echo mysqli_error($connection);
            }

            $lastid = $connection->insert_id;
            
            $connection->close();

            $connection = new mysqli($this->db_servername, $this->db_username, $this->db_password, $this->db_name);

            // echo "LAST INSERT ID: " + $lastid;

            // $query = "SELECT muistiinpano.*, muistiinpanotoken.token FROM muistiinpano INNER JOIN muistiinpano.id = muistiinpanotoken.muistiinpanoid WHERE muistiinpano.id = '$lastid'";
            $query = "SELECT muistiinpano.* , muistiinpanotoken.token FROM muistiinpano INNER JOIN kayttaja ON muistiinpano.omistaja = kayttaja.id LEFT JOIN muistiinpanotoken ON muistiinpano.id = muistiinpanotoken.muistiinpanoid WHERE muistiinpano.id = '$lastid'";

            $result = $connection->query($query);
            
            $muistiinpanot = array();
            $i = 0;

            if ($result->num_rows > 0) 
            {
                while($row = $result->fetch_assoc()) {
                    $id = $row["id"];
                    $omistaja = $row["omistaja"];
                    $otsikko = $row["otsikko"];
                    $data = $row["data"];
                    $lisatty = $row["lisatty"];
                    $token = $row["token"];
                    
                    $muistiinpano = new Muistiinpano($id, $omistaja, $otsikko, $data, $lisatty, $token);

                    $muistiinpanot[$i++] = $muistiinpano;
                }
            }
            

            $connection->close();
            return $muistiinpanot;
        }

        public function HaeMuistiinpanotOmistajalta($omistaja_id)
        {   // id, omistaja, otsikko, data, lisatty, vainlukuToken, kirjoitusToken
            // palauttaa muistiinpano-arrayn
            $connection = new mysqli($this->db_servername, $this->db_username, $this->db_password, $this->db_name);

            if ($connection->connect_error)
            {
                die("Ei saada yhteyttä tietokantaan.");
            }
            
            // $query = "SELECT muistiinpano.*, muistiinpanotoken.token FROM muistiinpano INNER JOIN muistiinpano.id = muistiinpanotoken.muistiinpanoid WHERE omistaja = '$omistaja_id'";
            $query = "SELECT muistiinpano.* , muistiinpanotoken.token FROM muistiinpano INNER JOIN kayttaja ON muistiinpano.omistaja = kayttaja.id LEFT JOIN muistiinpanotoken ON muistiinpano.id = muistiinpanotoken.muistiinpanoid WHERE omistaja = '$omistaja_id'";

            $result = $connection->query($query);
            
            $muistiinpanot = array();

            if ($result->num_rows > 0) 
            {
                while($row = $result->fetch_assoc()) {
                    $id = $row["id"];
                    $omistaja = $row["omistaja"];
                    $otsikko = $row["otsikko"];
                    $data = $row["data"];
                    $lisatty = $row["lisatty"];
                    $token = $row["muistiinpano.token"];
                    
                    $muistiinpano = new Muistiinpano($id, $omistaja, $otsikko, $data, $lisatty, $token);

                    $muistiinpanot[$i++] = $muistiinpano;
                }
            }
            
            $connection->close();

            return $muistiinpanot;

        }

        public function HaeMuistiinpanoTokenilla($token)
        {   // id, omistaja, otsikko, data, lisatty
            // palauttaa muistiinpano-arrayn
            $connection = new mysqli($this->db_servername, $this->db_username, $this->db_password, $this->db_name);

            if ($connection->connect_error)
            {
                die("Ei saada yhteyttä tietokantaan.");
            }

            $query = "SELECT muistiinpano.* , muistiinpanotoken.token FROM muistiinpano INNER JOIN kayttaja ON muistiinpano.omistaja = kayttaja.id LEFT JOIN muistiinpanotoken ON muistiinpano.id = muistiinpanotoken.muistiinpanoid WHERE muistiinpanotoken.token = '$token'";

            $result = $connection->query($query);
            
            $muistiinpanot = array();

            if ($result->num_rows > 0) 
            {
                while($row = $result->fetch_assoc()) {
                    $id = $row["id"];
                    $omistaja = $row["omistaja"];
                    $otsikko = $row["otsikko"];
                    $data = $row["data"];
                    $lisatty = $row["lisatty"];
                    $muistiinpanotoken = $row["token"];
                    // $vainlukuToken = $row["vainlukuToken"];
                    // $kirjoitusToken = $row["kirjoitusToken"];

                    $muistiinpano = new Muistiinpano($id, $omistaja, $otsikko, $data, $lisatty, $muistiinpanotoken);

                    $muistiinpanot[$i++] = $muistiinpano;
                }
            }
            else
            {
                echo mysqli_error($connection);
            }

            
            $connection->close();

            return $muistiinpanot;

        }

        public function HaeMuistiinpanotOmistajanTokenilla($token)
        {   // id, omistaja, otsikko, data, lisatty, vainlukuToken, kirjoitusToken
            // palauttaa muistiinpano-arrayn
            $connection = new mysqli($this->db_servername, $this->db_username, $this->db_password, $this->db_name);

            if ($connection->connect_error)
            {
                die("Ei saada yhteyttä tietokantaan.");
            }
            $query = "SELECT muistiinpano.* , muistiinpanotoken.token FROM muistiinpano INNER JOIN kayttaja ON muistiinpano.omistaja = kayttaja.id LEFT JOIN muistiinpanotoken ON muistiinpano.id = muistiinpanotoken.muistiinpanoid WHERE kayttaja.token = '$token'";

            $result = $connection->query($query);
            
            $muistiinpanot = array();
            $i = 0;

            if ($result->num_rows > 0) 
            {
                while($row = $result->fetch_assoc()) {
                    $id = $row["id"];
                    $omistaja = $row["omistaja"];
                    $otsikko = $row["otsikko"];
                    $data = $row["data"];
                    $lisatty = $row["lisatty"];
                    $muistiinpanotoken = $row["token"];

                    $muistiinpano = new Muistiinpano($id, $omistaja, $otsikko, $data, $lisatty, $muistiinpanotoken);

                    $muistiinpanot[$i++] = $muistiinpano;
                }
            }
            else
            {
                echo mysqli_error($connection);
            }
            
            $connection->close();

            return $muistiinpanot;

        }
    }
?>