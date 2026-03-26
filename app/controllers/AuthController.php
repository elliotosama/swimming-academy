<?php


// /app/controllers/AuthController.php


class AuthController {
	private UserModel $users;

	public function __construct() {
		$this->users = new UserModel();
	}


	// helpers

	private function redirect(string $path): void {
		header('Location: ' . APP_URL . $path);
		exit;
	}

	private function renderView(string $view, array $data = []): void {
		extract($data);
		require ROOT . "/views/{$view}.php";
	}

	private function flash (string $key, string $msg): void {
		$_SESSION[$key] = $msg;
	}

	// login function

	public function showLogin(): void{
		if(auth_check()) {
			auth_redirect_by_role();
		}

		$this->renderView('auth/login');
	}


	public function showDashboard(string $role): void {
		auth_require([$role]);

		$this->renderView("$role/dashboard");
	}


	public function handleLogin():void {
		$email = trim($_POST['email'] ?? '');
		$password = trim($_POST['password'] ?? '');

		if(!$email || !$password) {
			$this->flash('flash_error', 'Please enter your email and password');
			$this->redirect('/login');
		}

		$user = $this->users->findByEmail($email);

		if(!$user || !password_verify($password, $user['password_hash'])) {
			log_action("login_failed', 'email: {$email}");
			$this->flash('flash_error', 'Invalid email or password.');
			$this->redirect('/login');
		}

		auth_login_user($user);
		$this->users->updateLastLogin($user['id']);
		log_action('login_success', null, $user['id']);

		auth_redirect_by_role();
	}


	public function showRegister():void {
		if(auth_check()) {
			$this->auth_redirect_by_role();
		}
		$this->renderView('auth/register');
	}


	public function handleRegister(): void {
		$fullName  = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email']     ?? '');
        $password  = $_POST['password']       ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';

        // Validation
        $errors = [];
        if (strlen($fullName) < 2)
            $errors[] = 'Full name must be at least 2 characters.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $errors[] = 'Please enter a valid email address.';
        if (strlen($password) < 8)
            $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm)
            $errors[] = 'Passwords do not match.';
        if ($this->users->emailExists($email))
            $errors[] = 'An account with this email already exists.';

        if ($errors) {
            $this->flash('flash_error', implode('<br>', $errors));
            $this->flash('flash_old_email', $email);
            $this->flash('flash_old_name', $fullName);
            $this->redirect('/register');
        }

        $userId = $this->users->create([
            'full_name' => $fullName,
            'email'     => $email,
            'password'  => $password,
        ]);

        log_action('register', "email: {$email}", $userId);

        $this->flash('flash_success',
            'Account created!'
            . (!$sent ? ' (Email delivery failed — contact support)' : '')
        );
        $this->redirect('/login');
    }


    public function handleLogout(): void {
    	$userId = auth_user()['id'] ?? null;
    	log_action('logout', null, $userId);
    	auth_logout();
    	$this->flash('flash_success', 'You have been logged out.');
    	$this->redirect('/login');
    }

}