<?php
namespace controllers\publics;
	/**
	 * Page des users
	 */
	class User extends \Controller
	{
		/**
		 * Cette fonction est appelée avant toute les autres : 
		 * Elle vérifie que l'utilisateur est bien connecté
		 * @return void;
		 */
		public function _before()
        {
            global $bdd;
            $this->bdd = $bdd;

            $this->internalUser = new \controllers\internals\User($this->bdd);
            $this->internalEvent = new \controllers\internals\Event($this->bdd);

			\controllers\internals\Tool::verify_connect();
        }

		/**
		 * Cette fonction retourne tous les users, sous forme d'un tableau permettant l'administration de ces users
		 */	
        public function list ($page = 0)
        {
            $page = (int) $page;
            $users = $this->internalUser->get_list(25, $page);
            $this->render('user/list', ['users' => $users]);
        }    
		
		/**
         * Cette fonction va supprimer une liste de users
         * @param array int $_GET['ids'] : Les id des useres à supprimer
         * @return boolean;
         */
        public function delete ($csrf)
        {
            if (!$this->verifyCSRF($csrf))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return header('Location: ' . $this->generateUrl('User', 'list'));
            }

            if (!$_SESSION['user']['admin'])
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous devez être administrateur pour supprimer un utilisateur !');
                return header('Location: ' . $this->generateUrl('User', 'list'));
            }

            $ids = $_GET['ids'] ?? [];
            foreach ($ids as $id)
            {
                $this->internalUser->delete($id);
            }

            return header('Location: ' . $this->generateUrl('User', 'list'));
        }

		/**
		 * Cette fonction retourne la page d'ajout d'un user
		 */
		public function add()
		{
			return $this->render('user/add');
		}

		/**
		 * Cette fonction insert un nouveau user
		 * @param $csrf : Le jeton CSRF
         * @param string $_POST['email'] : L'email de l'utilisateur
         * @param string $_POST['email_confirm'] : Verif de l'email de l'utilisateur
         * @param optional string $_POST['password'] : Le mot de passe de l'utilisateur (si vide, généré automatiquement)
         * @param optional string $_POST['password_confirm'] : Confirmation du mot de passe de l'utilisateur
         * @param optional boolean $_POST['admin'] : Si vrai, l'utilisateur est admin, si vide non
		 */
		public function create($csrf)
		{
            if (!$this->verifyCSRF($csrf))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Jeton CSRF invalid !');
                return header('Location: ' . $this->generateUrl('User', 'add'));
            }
			
			$email = $_POST['email'] ?? false;
			$password = $_POST['password'] ?? \controllers\internals\Tool::generate_password(rand(6,12));
			$admin = $_POST['admin'] ?? false;

			if (!$email)
			{
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Vous devez au moins fournir une adresse e-mail pour l\'utilisateur.');
                return header('Location: ' . $this->generateUrl('User', 'add'));
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'L\'adresse e-mail n\'est pas valide.');
                return header('Location: ' . $this->generateUrl('User', 'add'));
            }

            $email_send = \controllers\internals\Tool::send_email($email, EMAIL_CREATE_USER, ['email' => $email, 'password' => $password]);
            if (!$email_send)
            {
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Impossible d\'envoyer l\'e-mail à l\'utilisateur, le compte n\'a donc pas été créé.');
                return header('Location: ' . $this->generateUrl('User', 'add'));
            }

            $user_id = $this->internalUser->create($email, $password, $admin);
			if (!$user_id)
			{
                \modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('danger', 'Impossible de créer ce user.');
				return header('Location: ' . $this->generateUrl('User', 'add'));
			}

			\modules\DescartesSessionMessages\internals\DescartesSessionMessages::push('success', 'L\'utilisateur a bien été créé.');
			return header('Location: ' . $this->generateUrl('User', 'list'));
		}
	}