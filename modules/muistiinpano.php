<?php
    class Muistiinpano implements JsonSerializable
    { // id, omistaja, otsikko, data, lisatty, vainlukuToken, kirjoitusToken
        private $id;
        private $omistaja_id;
        private $otsikko;
        private $data;
        private $lisatty;
        private $token;
       

        public function __construct($id, $omistaja_id, $otsikko, $data, $lisatty, $token)
        {
            $this->id = $id;
            $this->omistaja_id = $omistaja_id;
            $this->otsikko = $otsikko;
            $this->data = $data;
            $this->lisatty = $lisatty;
            $this->token = $token;
            
        }

        public function jsonSerialize()
        {
            return Array(
                'id' => $this->id,
                'omistaja' => $this->omistaja_id,
                'otsikko' => $this->otsikko,
                'data' => $this->data,
                'lisatty' => $this->lisatty,
                'token' => $this->token
            );
        }
        
        public function getId() { return $this->id; }
        public function getOmistajaId() { return $this->getOmistajaId; }
        public function getOtsikko() { return $this->otsikko; }
        public function getData() { return $this->data; }
        public function getLisatty() { return $this->lisatty; }
        public function getToken() { return $this->token; }
        

        public function setId($id) { $this->id = $id; }
        public function setOmistajaId($omistaja_id) { $this->omistaja_id = $omistaja_id; }
        public function setOtsikko($otsikko) { $this->otsikko = $otsikko; }
        public function setData($data) { $this->data = $data; }
        public function setLisatty($lisatty) { $this->lisatty = $lisatty; }
        public function setToken($token) { $this->token = $token; }
        

    }
?>