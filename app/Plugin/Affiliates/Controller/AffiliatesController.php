<?php
/**
 * CrowdFunding
 *
 * PHP version 5
 *
 * @category   PHP
 * @package    Crowdfunding
 * @subpackage Core
 * @author     Agriya <info@agriya.com>
 * @copyright  2018 Agriya Infoway Private Ltd
 * @license    http://www.agriya.com/ Agriya Infoway Licence
 * @link       http://www.agriya.com
 */
class AffiliatesController extends AppController
{
    public $name = 'Affiliates';
    public $permanentCacheAction = array(
        'user' => array(
            'index',
            'stats',
        ) ,
    );
    public function index() 
    {
        $this->pageTitle = __l('Affiliate');
        $conditions = array();
        $conditions['Affiliate.affliate_user_id'] = $this->Auth->user('id');
        $conditions['Affiliate.affiliate_status_id'] = array(
            ConstAffiliateStatus::PipeLine,
            ConstAffiliateStatus::Completed
        );
        if (isset($this->request->params['named']['filter_id'])) {
            $this->request->data['Affiliate']['affiliate_status_id'] = $this->request->params['named']['filter_id'];
        }
        if (!empty($this->request->data['Affiliate']['affiliate_status_id'])) {
            switch ($this->request->data['Affiliate']['affiliate_status_id']) {
                case ConstAffiliateStatus::PipeLine:
                    $conditions['Affiliate.affiliate_status_id'] = ConstAffiliateStatus::PipeLine;
                    $this->pageTitle.= ' - ' . __l('Pipeline');
                    break;

                case ConstAffiliateStatus::Completed:
                    $conditions['Affiliate.affiliate_status_id'] = ConstAffiliateStatus::Completed;
                    $this->pageTitle.= ' - ' . __l('Completed');
                    break;
            }
            $this->request->params['named']['filter_id'] = $this->request->data['Affiliate']['filter_id'];
        }
        $this->paginate = array(
            'conditions' => $conditions,
            'contain' => array(
                'AffiliateType',
                'AffiliateStatus',
                'Project' => array(
                    'User'
                ) ,
                'ProjectFund' => array(
                    'Project',
                    'User',
                ) ,
                'User',
            ) ,
            'order' => array(
                'Affiliate.id' => 'desc'
            ) ,
            'recursive' => 3
        );
        $this->set('affiliates', $this->paginate());
        $this->loadModel('Node');
        $node = $this->Node->find('first', array(
            'conditions' => array(
                'Node.id' => 1
            ) ,
            'recursive' => 0
        ));
        $this->set("slug", $node['Node']['slug']);
    }
    public function stats() 
    {
        $this->pageTitle = __l('Stats');
        $this->loadModel('Affiliate');
        $this->loadModel('AffiliateCashWithdrawal');
        $periods = array(
            'day' => array(
                'display' => __l('Today') ,
                'conditions' => array(
                    'created =' => date('Y-m-d', strtotime('now')) ,
                )
            ) ,
            'week' => array(
                'display' => __l('This Week') ,
                'conditions' => array(
                    'created >=' => date('Y-m-d', strtotime('now -7 days')) ,
                )
            ) ,
            'month' => array(
                'display' => __l('This Month') ,
                'conditions' => array(
                    'created >=' => date('Y-m-d', strtotime('now -30 days')) ,
                )
            ) ,
            'total' => array(
                'display' => __l('Total') ,
                'conditions' => array()
            )
        );
        $models[] = array(
            'Affiliate' => array(
                'display' => __l('Affiliate') ,
                'link' => array(
                    'controller' => 'affiliates',
                    'action' => 'index'
                ) ,
                'colspan' => 2
            )
        );
        $models[] = array(
            'Affiliate' => array(
                'display' => __l('Pipeline') ,
                'link' => array(
                    'controller' => 'affiliates',
                    'action' => 'index',
                    'filter_id' => ConstAffiliateStatus::PipeLine
                ) ,
                'conditions' => array(
                    'Affiliate.affiliate_status_id' => ConstAffiliateStatus::PipeLine,
                    'Affiliate.affliate_user_id' => $this->Auth->user('id')
                ) ,
                'alias' => 'AffiliatePipeLine',
                'type' => 'cCurrency',
                'isSub' => 'Affiliate'
            )
        );
        $models[] = array(
            'Affiliate' => array(
                'display' => __l('Completed') ,
                'link' => array(
                    'controller' => 'affiliates',
                    'action' => 'index',
                    'filter_id' => ConstAffiliateStatus::Completed
                ) ,
                'conditions' => array(
                    'Affiliate.affiliate_status_id' => ConstAffiliateStatus::Completed,
                    'Affiliate.affliate_user_id' => $this->Auth->user('id')
                ) ,
                'alias' => 'AffiliateCompleted',
                'type' => 'cCurrency',
                'isSub' => 'Affiliate'
            )
        );
        $models[] = array(
            'AffiliateCashWithdrawal' => array(
                'display' => __l('Affiliate Withdraw Requests') ,
                'colspan' => 5
            )
        );
        $models[] = array(
            'AffiliateCashWithdrawal' => array(
                'display' => __l('Pending') ,
                'link' => array(
                    'controller' => 'affiliate_cash_withdrawals',
                    'action' => 'index',
                    'filter_id' => ConstAffiliateCashWithdrawalStatus::Pending
                ) ,
                'conditions' => array(
                    'AffiliateCashWithdrawal.affiliate_cash_withdrawal_status_id' => ConstAffiliateCashWithdrawalStatus::Pending,
                    'AffiliateCashWithdrawal.user_id' => $this->Auth->user('id')
                ) ,
                'alias' => 'AffiliateCashWithdrawalPending',
                'type' => 'cCurrency',
                'isSub' => 'AffiliateCashWithdrawal'
            )
        );
        $models[] = array(
            'AffiliateCashWithdrawal' => array(
                'display' => __l('Approved') ,
                'link' => array(
                    'controller' => 'affiliate_cash_withdrawals',
                    'action' => 'index',
                    'filter_id' => ConstAffiliateCashWithdrawalStatus::Approved
                ) ,
                'conditions' => array(
                    'AffiliateCashWithdrawal.affiliate_cash_withdrawal_status_id' => ConstAffiliateCashWithdrawalStatus::Approved,
                    'AffiliateCashWithdrawal.user_id' => $this->Auth->user('id')
                ) ,
                'alias' => 'AffiliateCashWithdrawalApproved',
                'type' => 'cCurrency',
                'isSub' => 'AffiliateCashWithdrawal'
            )
        );
        $models[] = array(
            'AffiliateCashWithdrawal' => array(
                'display' => __l('Reject') ,
                'link' => array(
                    'controller' => 'affiliate_cash_withdrawals',
                    'action' => 'index',
                    'filter_id' => ConstAffiliateCashWithdrawalStatus::Rejected
                ) ,
                'conditions' => array(
                    'AffiliateCashWithdrawal.affiliate_cash_withdrawal_status_id' => ConstAffiliateCashWithdrawalStatus::Rejected,
                    'AffiliateCashWithdrawal.user_id' => $this->Auth->user('id')
                ) ,
                'alias' => 'AffiliateCashWithdrawalReject',
                'type' => 'cCurrency',
                'isSub' => 'AffiliateCashWithdrawal'
            )
        );
        $models[] = array(
            'AffiliateCashWithdrawal' => array(
                'display' => __l('Success') ,
                'link' => array(
                    'controller' => 'affiliate_cash_withdrawals',
                    'action' => 'index',
                    'filter_id' => ConstAffiliateCashWithdrawalStatus::Success
                ) ,
                'conditions' => array(
                    'AffiliateCashWithdrawal.affiliate_cash_withdrawal_status_id' => ConstAffiliateCashWithdrawalStatus::Success,
                    'AffiliateCashWithdrawal.user_id' => $this->Auth->user('id')
                ) ,
                'alias' => 'AffiliateCashWithdrawalSuccess',
                'type' => 'cCurrency',
                'isSub' => 'AffiliateCashWithdrawal'
            )
        );
        foreach($models as $unique_model) {
            foreach($unique_model as $model => $fields) {
                foreach($periods as $key => $period) {
                    $conditions = $period['conditions'];
                    if (!empty($fields['conditions'])) {
                        $conditions = array_merge($periods[$key]['conditions'], $fields['conditions']);
                    }
                    $aliasName = !empty($fields['alias']) ? $fields['alias'] : $model;
                    if ($model == 'Affiliate') {
                        $AffiliateStatus = $this->Affiliate->find('first', array(
                            'conditions' => $conditions,
                            'fields' => array(
                                'SUM(Affiliate.commission_amount) as commission_amount'
                            ) ,
                            'recursive' => -1
                        ));
                        $this->set($aliasName . $key, $AffiliateStatus['0']['commission_amount']);
                    } else if ($model == 'AffiliateCashWithdrawal') {
                        $AffiliateCashWithdrawalStatus = $this->AffiliateCashWithdrawal->find('first', array(
                            'conditions' => $conditions,
                            'fields' => array(
                                'SUM(AffiliateCashWithdrawal.amount) as amount'
                            ) ,
                            'recursive' => -1
                        ));
                        $this->set($aliasName . $key, $AffiliateCashWithdrawalStatus['0']['amount']);
                    } else {
                        $new_periods = $period;
                        foreach($new_periods['conditions'] as $p_key => $p_value) {
                            unset($new_periods['conditions'][$p_key]);
                            $new_periods['conditions'][str_replace('created', $model . '.created', $p_key) ] = $p_value;
                        }
                        $conditions = $new_periods['conditions'];
                        if (!empty($fields['conditions'])) {
                            $conditions = array_merge($new_periods['conditions'], $fields['conditions']);
                        }
                        $this->set($aliasName . $key, $this->{$model}->find('count', array(
                            'conditions' => $conditions,
                            'recursive' => -1
                        )));
                    }
                }
            }
        }
        $this->set(compact('periods', 'models'));
    }
    public function admin_index() 
    {
        $this->_redirectGET2Named(array(
            'filter_id',
            'q'
        ));
        $this->pageTitle = __l('Affiliates');
        $conditions = array();
        if (isset($this->request->params['named']['q'])) {
            $this->request->data['Affiliate']['q'] = $this->request->params['named']['q'];
            $this->pageTitle.= sprintf(__l(' - Search - %s') , $this->request->params['named']['q']);
        }
        if (!empty($this->request->params['named']['filter_id'])) {
            if ($this->request->params['named']['filter_id'] == ConstAffiliateStatus::Pending) {
                $conditions['Affiliate.affiliate_status_id'] = ConstAffiliateStatus::Pending;
                $this->pageTitle.= ' - ' . __l('Pending');
            } else if ($this->request->params['named']['filter_id'] == ConstAffiliateStatus::Canceled) {
                $conditions['Affiliate.affiliate_status_id'] = ConstAffiliateStatus::Canceled;
                $this->pageTitle.= ' - ' . __l('Canceled');
            } else if ($this->request->params['named']['filter_id'] == ConstAffiliateStatus::PipeLine) {
                $conditions['Affiliate.affiliate_status_id'] = ConstAffiliateStatus::PipeLine;
                $this->pageTitle.= ' - ' . __l('Pipeline');
            } else if ($this->request->params['named']['filter_id'] == ConstAffiliateStatus::Completed) {
                $conditions['Affiliate.affiliate_status_id'] = ConstAffiliateStatus::Completed;
                $this->pageTitle.= ' - ' . __l('Completed');
            }
            if (!empty($this->request->data['Affiliate']['filter_id'])) {
                $this->request->params['named']['filter_id'] = $this->request->data['Affiliate']['filter_id'];
            }
        }
        $filters = $this->Affiliate->AffiliateStatus->find('list');
        $this->paginate = array(
            'conditions' => $conditions,
            'contain' => array(
                'AffiliateUser',
                'AffiliateType',
                'AffiliateStatus',
            ) ,
            'order' => array(
                'Affiliate.id' => 'desc'
            ) ,
            'recursive' => 2
        );
        if (!empty($this->request->data['Affiliate']['q'])) {
            $this->paginate = array_merge($this->paginate, array(
                'search' => $this->request->data['Affiliate']['q']
            ));
        }
        $this->set('affiliates', $this->paginate());
        // Stats
        $this->set('pending', $this->Affiliate->find('count', array(
            'conditions' => array(
                'Affiliate.affiliate_status_id' => ConstAffiliateStatus::Pending
            )
        )));
        $this->set('canceled', $this->Affiliate->find('count', array(
            'conditions' => array(
                'Affiliate.affiliate_status_id' => ConstAffiliateStatus::Canceled
            )
        )));
        $this->set('pipeline', $this->Affiliate->find('count', array(
            'conditions' => array(
                'Affiliate.affiliate_status_id' => ConstAffiliateStatus::PipeLine
            )
        )));
        $this->set('completed', $this->Affiliate->find('count', array(
            'conditions' => array(
                'Affiliate.affiliate_status_id' => ConstAffiliateStatus::Completed
            )
        )));
        $this->set('all', $this->Affiliate->find('count'));
        $this->set(compact('filters'));
    }
    public function admin_delete($id = null) 
    {
        if (is_null($id)) {
            throw new NotFoundException(__l('Invalid request'));
        }
        if ($this->Affiliate->delete($id)) {
            $this->Session->setFlash(sprintf(__l('%s deleted') , __l('Affiliate')) , 'default', null, 'success');
            $this->redirect(array(
                'action' => 'index'
            ));
        } else {
            throw new NotFoundException(__l('Invalid request'));
        }
    }
    public function admin_stats() 
    {
        $this->pageTitle = __l('Stats');
        $this->loadModel('AffiliateRequest');
        $this->loadModel('Affiliate');
        $this->loadModel('AffiliateCashWithdrawal');
        $periods = array(
            'day' => array(
                'display' => __l('Today') ,
                'conditions' => array(
                    'created >=' => date('Y-m-d 00:00:00') ,
                    'created <=' => date('Y-m-d 23:59:59')
                )
            ) ,
            'week' => array(
                'display' => __l('This Week') ,
                'conditions' => array(
                    'created >=' => date('Y-m-d', strtotime('now -7 days')) ,
                )
            ) ,
            'month' => array(
                'display' => __l('This Month') ,
                'conditions' => array(
                    'created >=' => date('Y-m-d', strtotime('now -30 days')) ,
                )
            ) ,
            'total' => array(
                'display' => __l('Total') ,
                'conditions' => array()
            )
        );
        $models[] = array(
            'Affiliate' => array(
                'display' => __l('Affiliates') ,
                'link' => array(
                    'controller' => 'affiliates',
                    'action' => 'index'
                ) ,
                'colspan' => 4
            )
        );
        $models[] = array(
            'Affiliate' => array(
                'display' => __l('Pending') ,
                'link' => array(
                    'controller' => 'affiliates',
                    'action' => 'index',
                    'filter_id' => ConstAffiliateStatus::Pending,
                    'admin' => true
                ) ,
                'conditions' => array(
                    'Affiliate.affiliate_status_id' => ConstAffiliateStatus::Pending,
                ) ,
                'alias' => 'AffiliatePending',
                'isSub' => 'Affiliate'
            )
        );
        $models[] = array(
            'Affiliate' => array(
                'display' => __l('Canceled') ,
                'link' => array(
                    'controller' => 'affiliates',
                    'action' => 'index',
                    'filter_id' => ConstAffiliateStatus::Canceled,
                    'admin' => true
                ) ,
                'conditions' => array(
                    'Affiliate.affiliate_status_id' => ConstAffiliateStatus::Canceled,
                ) ,
                'alias' => 'AffiliateCanceled',
                'isSub' => 'Affiliate'
            )
        );
        $models[] = array(
            'Affiliate' => array(
                'display' => __l('Pipeline') ,
                'link' => array(
                    'controller' => 'affiliates',
                    'action' => 'index',
                    'filter_id' => ConstAffiliateStatus::PipeLine,
                    'admin' => true
                ) ,
                'conditions' => array(
                    'Affiliate.affiliate_status_id' => ConstAffiliateStatus::PipeLine,
                ) ,
                'alias' => 'AffiliatePipeLine',
                'isSub' => 'Affiliate'
            )
        );
        $models[] = array(
            'Affiliate' => array(
                'display' => __l('Completed') ,
                'link' => array(
                    'controller' => 'affiliates',
                    'action' => 'index',
                    'filter_id' => ConstAffiliateStatus::Completed,
                    'admin' => true
                ) ,
                'conditions' => array(
                    'Affiliate.affiliate_status_id' => ConstAffiliateStatus::Completed,
                ) ,
                'alias' => 'AffiliateCompleted',
                'isSub' => 'Affiliate'
            )
        );
        $models[] = array(
            'Affiliate' => array(
                'display' => __l('Affiliate Requests') ,
                'isNeedLoop' => false,
                'alias' => 'Affiliate',
                'colspan' => 3
            ) ,
        );
        $models[] = array(
            'AffiliateRequest' => array(
                'display' => __l('Approved') ,
                'conditions' => array(
                    'AffiliateRequest.is_approved' => 1
                ) ,
                'link' => array(
                    'controller' => 'affiliate_requests',
                    'action' => 'index',
                    'is_approved' => 1,
                    'admin' => true
                ) ,
                'alias' => 'AffiliateRequestApproved',
                'isSub' => 'Affiliate'
            ) ,
        );
        $models[] = array(
            'AffiliateRequest' => array(
                'display' => __l('Waiting for Approved') ,
                'conditions' => array(
                    'AffiliateRequest.is_approved' => 0
                ) ,
                'link' => array(
                    'controller' => 'affiliate_requests',
                    'action' => 'index',
                    'is_approved' => 0,
                    'admin' => true
                ) ,
                'alias' => 'AffiliateRequest',
                'isSub' => 'Affiliate'
            ) ,
        );
        $models[] = array(
            'AffiliateRequest' => array(
                'display' => __l('Total') ,
                'conditions' => array() ,
                'link' => array(
                    'controller' => 'affiliate_requests',
                    'action' => 'index',
                    'admin' => true
                ) ,
                'alias' => 'AffiliateRequestTotal',
                'isSub' => 'Affiliate'
            ) ,
        );
        $models[] = array(
            'AffiliateCashWithdrawal' => array(
                'display' => __l('Affiliate Withdraw Requests') ,
                'link' => array(
                    'controller' => 'affiliates',
                    'action' => 'index'
                ) ,
                'colspan' => 5
            )
        );
        $models[] = array(
            'AffiliateCashWithdrawal' => array(
                'display' => __l('Pending') ,
                'link' => array(
                    'controller' => 'affiliate_cash_withdrawals',
                    'action' => 'index',
                    'filter_id' => ConstAffiliateCashWithdrawalStatus::Pending,
                    'admin' => true
                ) ,
                'conditions' => array(
                    'AffiliateCashWithdrawal.affiliate_cash_withdrawal_status_id' => ConstAffiliateCashWithdrawalStatus::Pending,
                ) ,
                'alias' => 'AffiliateCashWithdrawalPending',
                'isSub' => 'AffiliateCashWithdrawal'
            )
        );
        $models[] = array(
            'AffiliateCashWithdrawal' => array(
                'display' => __l('Approved') ,
                'link' => array(
                    'controller' => 'affiliate_cash_withdrawals',
                    'action' => 'index',
                    'filter_id' => ConstAffiliateCashWithdrawalStatus::Approved,
                    'admin' => true
                ) ,
                'conditions' => array(
                    'AffiliateCashWithdrawal.affiliate_cash_withdrawal_status_id' => ConstAffiliateCashWithdrawalStatus::Approved,
                ) ,
                'alias' => 'AffiliateCashWithdrawalApproved',
                'isSub' => 'AffiliateCashWithdrawal'
            )
        );
        $models[] = array(
            'AffiliateCashWithdrawal' => array(
                'display' => __l('Reject') ,
                'link' => array(
                    'controller' => 'affiliate_cash_withdrawals',
                    'action' => 'index',
                    'filter_id' => ConstAffiliateCashWithdrawalStatus::Rejected,
                    'admin' => true
                ) ,
                'conditions' => array(
                    'AffiliateCashWithdrawal.affiliate_cash_withdrawal_status_id' => ConstAffiliateCashWithdrawalStatus::Rejected,
                ) ,
                'alias' => 'AffiliateCashWithdrawalReject',
                'isSub' => 'AffiliateCashWithdrawal'
            )
        );
        $models[] = array(
            'AffiliateCashWithdrawal' => array(
                'display' => __l('Success') ,
                'link' => array(
                    'controller' => 'affiliate_cash_withdrawals',
                    'action' => 'index',
                    'filter_id' => ConstAffiliateCashWithdrawalStatus::Success,
                    'admin' => true
                ) ,
                'conditions' => array(
                    'AffiliateCashWithdrawal.affiliate_cash_withdrawal_status_id' => ConstAffiliateCashWithdrawalStatus::Success,
                ) ,
                'alias' => 'AffiliateCashWithdrawalSuccess',
                'isSub' => 'AffiliateCashWithdrawal'
            )
        );
        foreach($models as $unique_model) {
            foreach($unique_model as $model => $fields) {
                foreach($periods as $key => $period) {
                    $conditions = $period['conditions'];
                    if (!empty($fields['conditions'])) {
                        $conditions = array_merge($periods[$key]['conditions'], $fields['conditions']);
                    }
                    $aliasName = !empty($fields['alias']) ? $fields['alias'] : $model;
                    $new_periods = $period;
                    foreach($new_periods['conditions'] as $p_key => $p_value) {
                        unset($new_periods['conditions'][$p_key]);
                        $new_periods['conditions'][str_replace('created', $model . '.created', $p_key) ] = $p_value;
                    }
                    $conditions = $new_periods['conditions'];
                    if (!empty($fields['conditions'])) {
                        $conditions = array_merge($new_periods['conditions'], $fields['conditions']);
                    }
                    $this->set($aliasName . $key, $this->{$model}->find('count', array(
                        'conditions' => $conditions,
                    )));
                }
            }
        }
        $this->set(compact('periods', 'models'));
    }
}
?>