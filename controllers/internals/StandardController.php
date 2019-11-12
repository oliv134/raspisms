<?php

/*
 * This file is part of RaspiSMS.
 *
 * (c) Pierre-Lin Bonnemaison <plebwebsas@gmail.com>
 *
 * This source file is subject to the GPL-3.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace controllers\internals;

    abstract class StandardController extends \descartes\InternalController
    {
        public function __construct(\PDO $bdd)
        {
            $this->bdd = $bdd;
        }

        /**
         * Get the model for the Controller
         * @return \descartes\Model
         */
        abstract protected function get_model () : \descartes\Model;


        /**
         * Create a new entry
         * @return mixed bool|int : False if cannot create entry, id of the new entry else
         */
        abstract public function create();
        
        
        /**
         * Update a entry
         * @return mixed bool|int : False if cannot update entry, number of modified rows else
         */
        abstract public function update_for_user();
        

        /**
         * Return a entry by his id
         * @param int $id : Entry id
         * @return array
         */
        public function get (int $id)
        {
            return $this->get_model()->get($id);
        }


        /**
         * Return the list of entries for a user
         * @param int $id_user : User id
         * @param ?int $nb_entry : Number of entry to return
         * @param ?int $page     : Pagination, used to calcul offset, $nb_entry * $page
         * @return array : Entrys list
         */
        public function list_for_user (int $id_user, ?int $nb_entry = null, ?int $page = null)
        {
            return $this->get_model()->list_for_user($id_user, $nb_entry, $nb_entry * $page);
        }


        /**
         * Return a list of entries in a group of ids and for a user
         * @param int $id_user : user id
         * @param array $ids : ids of entries to find
         * @return array
         */
        public function gets_in_for_user (int $id_user, array $ids)
        {
            return $this->get_model()->gets_in_for_user($id_user, $ids);
        }


        /**
         * Insert a entry
         * @param 
         * @param array $entry : Entry to insert
         * @return mixed bool|int : false on error, new entry id else
         */
        public function create ($entry)
        {
            $result = $this->get_model()->insert($entry);
        }

        
        /**
         * Delete a entry by his id for a user
         * @param int $id_user : User id
         * @param int $id : Entry id
         * @return int : Number of removed rows
         */
        public function delete_for_user(int $id_user, int $id)
        {
            return $this->get_model()->delete_for_user($id_user, $id);
        }


        /**
         * Count number of entry for a user
         * @param int $id_user : User id
         * @return int : number of entries
         */
        public function count_for_user(int $id_user)
        {
            return $this->get_model()->count_for_user($id_user);
        }
    }