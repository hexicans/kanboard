<?php

namespace Kanboard\Controller;

/**
 * Class UserCredentialController
 *
 * @package Kanboard\Controller
 * @author  Frederic Guillot
 */
class UserCredentialController extends BaseController
{
    /**
     * Password modification form
     *
     * @access public
     * @param array $values
     * @param array $errors
     * @throws \Kanboard\Core\Controller\AccessForbiddenException
     * @throws \Kanboard\Core\Controller\PageNotFoundException
     */
    public function changePassword(array $values = array(), array $errors = array())
    {
        $user = $this->getUser();

        return $this->response->html($this->helper->layout->user('user_credential/password', array(
            'values' => $values + array('id' => $user['id']),
            'errors' => $errors,
            'user' => $user,
        )));
    }

    /**
     * Save new password
     *
     * @throws \Kanboard\Core\Controller\AccessForbiddenException
     * @throws \Kanboard\Core\Controller\PageNotFoundException
     */
    public function savePassword()
    {
        $user = $this->getUser();
        $values = $this->request->getValues();

        list($valid, $errors) = $this->userValidator->validatePasswordModification($values);

        if ($valid) {
            if ($this->user->update($values)) {
                $this->flash->success(t('Password modified successfully.'));
                $this->userLocking->resetFailedLogin($user['username']);
            } else {
                $this->flash->failure(t('Unable to change the password.'));
            }

            return $this->response->redirect($this->helper->url->to('UserViewController', 'show', array('user_id' => $user['id'])));
        }

        return $this->changePassword($values, $errors);
    }

    /**
     * Display a form to edit authentication
     *
     * @access public
     * @param array $values
     * @param array $errors
     * @throws \Kanboard\Core\Controller\AccessForbiddenException
     * @throws \Kanboard\Core\Controller\PageNotFoundException
     */
    public function changeAuthentication(array $values = array(), array $errors = array())
    {
        $user = $this->getUser();

        if (empty($values)) {
            $values = $user;
            unset($values['password']);
        }

        return $this->response->html($this->helper->layout->user('user_credential/authentication', array(
            'values' => $values,
            'errors' => $errors,
            'user' => $user,
        )));
    }

    /**
     * Save authentication
     *
     * @throws \Kanboard\Core\Controller\AccessForbiddenException
     * @throws \Kanboard\Core\Controller\PageNotFoundException
     */
    public function saveAuthentication()
    {
        $user = $this->getUser();
        $values = $this->request->getValues() + array('disable_login_form' => 0, 'is_ldap_user' => 0);
        list($valid, $errors) = $this->userValidator->validateModification($values);

        if ($valid) {
            if ($this->user->update($values)) {
                $this->flash->success(t('User updated successfully.'));
            } else {
                $this->flash->failure(t('Unable to update your user.'));
            }

            return $this->response->redirect($this->helper->url->to('UserCredentialController', 'changeAuthentication', array('user_id' => $user['id'])));
        }

        return $this->changeAuthentication($values, $errors);
    }
}