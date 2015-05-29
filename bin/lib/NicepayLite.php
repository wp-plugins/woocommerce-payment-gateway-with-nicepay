<?php
extract($_POST);
extract($_GET);

/*____________________________________________________________

Copyright (C) 2014 NICE IT&T
*
* �ش� ���̺귯���� �����Ͻô°�� ���ι� ��ҿ� ������ �߻��� �� �ֽ��ϴ�.
* ���Ƿ� ������ �ڵ忡 ���� å���� �������� �����ڿ��� ������ �˷��帳�ϴ�.

*	@ description		: SSL ���� ����� ����Ѵ�.
*	@ name				: NicepayLite.php
*	@ auther				: NICEPAY I&T (tech@nicepay.co.kr)
*	@ date				: 
*	@ modify			
	
	2013.05.24			Update Log
	
*____________________________________________________________
*/
require_once('NicepayLiteLog.php');
require_once('NicepayLiteCommon.php');

class NicepayLite
{
	// configuration Parameter
	var $m_NicepayHome;			// �α� ���
	
	
	// requestPage Parameter
	var $m_EdiDate;				// ó�� �Ͻ�
	var $m_MerchantKey;			// ������ �ο��� ���� Ű
	var $m_Price;				// ���� �ݾ�
	var $m_HashedString;		// �ֿ� ������ hash��
	var $m_VBankExpDate;		// ������� �Ա� ������
	var $m_MerchantServerIp;	// ���� ���� ������
	var $m_UserIp;				// ������ ������
	
	// resultPage Parameter
	var $m_GoodsName;			// ��ǰ��
	var $m_Amt;					// ��ǰ ����
	var $m_Moid;				// ���� �ֹ���ȣ
	var $m_BuyerName;			// ������ �̸�
	var $m_BuyerEmail;			// ������ �̸���
	var $m_BuyerTel;			// ������ ��ȭ��ȣ
	var $m_MallUserID;			// ������ ���� ���̵�
	var $m_MallReserved;		// ���� �����ʵ�
	var $m_GoodsCl;				// ��ǰ ����
	var $m_MID;					// ���� ���̵�
	var $m_MallIP;				// ���� ���� ������ **
	var $m_TrKey;				// ��ȣȭ ������
	var $m_EncryptedData;		// ���� ��ȣȭ ������
	var $m_PayMethod;			// ���� ����
	var $m_TransType;			
	var $m_ActionType;			
	var $m_LicenseKey;
	
	
	var $m_ReceiptAmt;  //���ݿ����� �߱� �ݾ� 
	var $m_ReceiptSupplyAmt;  //���ݿ����� ���޾� 
	var $m_ReceiptVAT;  //���ݿ����� �ΰ����� 
	var $m_ReceiptServiceAmt;  //���ݿ����� ���񽺾� 
	var $m_ReceiptType;   //
	var $m_ReceiptTypeNo; //
	
	
	// payResult 
	var $m_ResultCode;			// ��� �ڵ�
	var $m_ResultMsg;			// ��� �޽���
	var $m_ErrorCD;				// ���� �ڵ�
	var $m_ErrorMsg;			// �����޽���
	var $m_AuthDate;			// ���� �ð�
	var $m_AuthCode;			// ���� ��ȣ
	//var $m_BuyerName;
	//var $m_MallUserID;
	//var $m_GoodsName;
	//var $m_PayMethod;
	//var $m_MID;
	var $m_TID;					// �ŷ� ���̵�
	//var $m_Moid;
	//var $m_Amt;
	var $m_CardCode;			// ī�� �ڵ�
	var $m_CardName;			// ���� ī��� �̸�
	var $m_CardNo;				// ī�� ��ȣ
	var $m_BankCode;			// ���� �ڵ�
	var $m_BankName;			// ���� ���� �̸�
	var $m_Carrier;				// ����� �ڵ�
	var $m_DestAddr;			//
	var $m_VbankBankCode;		// ������� ���� �ڵ�
	var $m_VbankBankName;		// ������� ���� �̸�
	var $m_VbankNum;			// ������� ��ȣ
	var $m_CardQuota;			// �Һΰ���	

	var $m_charSet;				// ĳ���ͼ�
	
	// ��� ����
	var $m_CancelAmt;			// ��� �ݾ�
	var $m_CancelMsg;			// ��� �޽���
	var $m_CancelPwd;           // ��� �н�����
	var $m_PartialCancelCode; 	// �κ���� �ڵ�

	var $m_ExpDate;				// �Ա� ��������
	var $m_ReqName;			// �Ա���
	var $m_ReqTel;				// �Ա��� ����ó
	
	// ����
	var $m_uri;					// ó�� uri
	var $m_ssl;					// �������� ����
	var $m_queryString = array(); // ���� ��Ʈ��
	var $m_ResultData = array();  // ��� array
	
	// ���� ����
	var $m_BillKey;             // ��Ű
	var $m_ExpYear;             // ī�� ��ȿ�Ⱓ
	var $m_ExpMonth;             // ī�� ��ȿ�Ⱓ
	var $m_IDNo;                // �ֹι�ȣ
	var $m_CardPwd;             // ī�� ��й�ȣ

	var $m_CartType;			// ��ٱ��� ���� �Ǻ� ����
	
	var $m_DeliveryCoNm;		// ��� ��ü
	var $m_InvoiceNum;			// ���� ��ȣ
	var $m_BuyerAddr;			// ������ּ�
	var $m_RegisterName;		// ������̸�
	var $m_BuyerAuthNum;		// �ĺ��� (�ֹι�ȣ)
	var $m_ReqType;				// ��û Ÿ��
	var $m_ConfirmMail;			// �̸��� �߼� ����

	
	var $m_log;					// �α� ��� ����
	var $m_debug;			//  �α� Ÿ�� ����
	
	
	
	// �� 4������ ���� �ؾ���.
	// 1. �� �ֿ� �ʵ��� hash ������
	// 2. ������� �Ա��� ���� 
	// 3. ����� IP ����
	// 4. ���� ���� ������ ����
	function requestProcess() {
		// hash ó��
		$this->m_EdiDate = date("YmdHis");
		$str_temp = $this->m_EdiDate.$this->m_MID.$this->m_Price.$this->m_MerchantKey;
		//echo($str_temp);
		$this->m_HashedString = base64_encode( md5($str_temp ));
		
		// ������� �Ա��� ����
		$this->m_VBankExpDate = date("Ymd",strtotime("+3 day",time()));
		
		// ����� IP ����
		$this->m_UserIp = $_SERVER['REMOTE_ADDR'];
		
		// ���� ���������� ����
		$this->m_MerchantServerIp = $_SERVER['SERVER_ADDR'];
	}
	
	// https connection �� �ؼ� ���� ��û�� ��.
	function startAction() {
		if (trim($this->m_ActionType) == "" ) {
			$this->MakeErrorMsg( ERR_WRONG_ACTIONTYPE , "actionType ������ �߸��Ǿ����ϴ�."); 
			return;
		}
		
		$NICELog = new NICELog( $this->m_log, $this->m_debug, $this->m_ActionType );
				
		if(!$NICELog->StartLog($this->m_NicepayHome,$this->m_MID)) 
		{
			$this->MakeErrorMsg( ERR_OPENLOG, "�α������� ������ �����ϴ�."); 
			return;
		}
		
		// ����� ���,
		if (trim($this->m_ActionType) == "CLO" ) {	
			if(trim($this->m_TID) == "") {
				$this->MakeErrorMsg( ERR_WRONG_PARAMETER, "��û������ �Ķ���Ͱ� �߸��Ǿ����ϴ�. [TID]"); 
				return;
			} else if (trim($this->m_CancelAmt) == "" ) {
				$this->MakeErrorMsg( ERROR_WRONG_PARAMETER, "��û������ �Ķ���Ͱ� �߸��Ǿ����ϴ�. [CancelAmt]"); 
				return;
			} else if (trim($this->m_CancelMsg) == "" ) {
				$this->MakeErrorMsg( ERROR_WRONG_PARAMETER, "��û������ �Ķ���Ͱ� �߸��Ǿ����ϴ�. [CancelMsg]"); 
				return;
			} 
			
			$this->m_uri = "/lite/cancelProcess.jsp";
			unset($this->m_queryString);
			$this->m_queryString = $_POST;
			$this->m_queryString["MID"] = substr($this->m_TID, 0,10);
			$this->m_queryString["TID"] = $this->m_TID;
			$this->m_queryString["CancelAmt"] = $this->m_CancelAmt;
			$this->m_queryString["CancelMsg"] = $this->m_CancelMsg;
			$this->m_queryString["CancelPwd"] = $this->m_CancelPwd;
			$this->m_queryString["PartialCancelCode"] = $this->m_PartialCancelCode;
			
			$NICELog->WriteLog($this->m_queryString["TID"] );
		//�Ա� �� ���
		}else if (trim($this->m_ActionType) == "DPO" ) {	
			if(trim($this->m_TID) == "") {
				$this->MakeErrorMsg( ERR_WRONG_PARAMETER, "��û������ �Ķ���Ͱ� �߸��Ǿ����ϴ�. [TID]"); 
				return;
			} else if (trim($this->m_CancelAmt) == "" ) {
				$this->MakeErrorMsg( ERROR_WRONG_PARAMETER, "��û������ �Ķ���Ͱ� �߸��Ǿ����ϴ�. [CancelAmt]"); 
				return;
			} else if (trim($this->m_CancelMsg) == "" ) {
				$this->MakeErrorMsg( ERROR_WRONG_PARAMETER, "��û������ �Ķ���Ͱ� �߸��Ǿ����ϴ�. [CancelMsg]"); 
				return;
			} 
			
			$this->m_uri = "/lite/setOffProcess.jsp";
			unset($this->m_queryString);
			$this->m_queryString["MID"] = substr($this->m_TID, 0,10);
			$this->m_queryString["TID"] = $this->m_TID;
			$this->m_queryString["CancelAmt"] = $this->m_CancelAmt;
			$this->m_queryString["CancelMsg"] = $this->m_CancelMsg;
			$this->m_queryString["PartialCancelCode"] = $this->m_PartialCancelCode;
			$this->m_queryString["ExpDate"]	= $this->m_ExpDate;
			$this->m_queryString["ReqName"]	= $this->m_ReqName;
			$this->m_queryString["ReqTel"]		= $this->m_ReqTel;
			$NICELog->WriteLog($this->m_queryString["TID"] );
		
		// ���� ����	
		} else if (trim($this->m_ActionType) == "PYO" && trim($this->m_PayMethod) == "BILL" ) {
		  
		   $this->m_uri = "/lite/billingProcess.jsp";
		 
			unset($this->m_queryString);
			$this->m_queryString = $_POST;
			$this->m_queryString["BillKey"] = $this->m_BillKey;   // new
			$this->m_queryString["BuyerName"] = $this->m_BuyerName;
			$this->m_queryString["Amt"] = $this->m_Amt;
			$this->m_queryString["MID"] = $this->m_MID;
			$this->m_TID = genTID( $this->m_MID,"01","16");
			$this->m_queryString["TID"] = $this->m_TID;
			$this->m_queryString["EncodeKey"] = $this->m_LicenseKey;
			$this->m_queryString["MallIP"] = $_SERVER['SERVER_NAME'];
			$this->m_queryString["actionType"] = $this->m_ActionType;
			$this->m_queryString["PayMethod"] = $this->m_PayMethod;
			$this->m_queryString["Moid"] = $this->m_Moid;
			$this->m_queryString["GoodsName"] = $this->m_GoodsName;
			$this->m_queryString["CardQuota"] = $this->m_CardQuota;

			if($this->m_charSet =="UTF8"){
				$this->m_queryString["BuyerName"] = iconv("UTF-8", "EUC-KR",$this->m_queryString["BuyerName"]);
				$this->m_queryString["GoodsName"] = iconv("UTF-8", "EUC-KR",$this->m_queryString["GoodsName"]);
			}
			
		   $NICELog->WriteLog($this->m_queryString["TID"]);
		   
		
		
		} else if (trim($this->m_ActionType) == "PYO" && trim($this->m_PayMethod) == "ESCROW" ) {
		    // ����ũ�� ��� ���
		  $this->m_uri = "/lite/escrowProcess.jsp";
			unset($this->m_queryString);
			$this->m_queryString["MID"] = $this->m_MID;
			$this->m_queryString["TID"] = $this->m_TID;
			$this->m_queryString["DeliveryCoNm"] = $this->m_DeliveryCoNm;
			$this->m_queryString["InvoiceNum"] = $this->m_InvoiceNum;
			$this->m_queryString["BuyerAddr"] = $this->m_BuyerAddr;
			$this->m_queryString["RegisterName"] = $this->m_RegisterName;
			$this->m_queryString["BuyerAuthNum"] = $this->m_BuyerAuthNum;
			$this->m_queryString["ReqType"] = $this->m_ReqType;
			$this->m_queryString["MallIP"] = $_SERVER['SERVER_NAME'];
			$this->m_queryString["actionType"] = $this->m_ActionType;
			$this->m_queryString["PayMethod"] = $this->m_PayMethod;
			$this->m_queryString["EncodeKey"] = $this->m_LicenseKey;
			$this->m_queryString["ConfirmMail"] = $this->m_ConfirmMail;
			if($this->m_charSet =="UTF8"){
				$this->m_queryString["BuyerAddr"] = iconv("UTF-8", "EUC-KR",$this->m_BuyerAddr);
				$this->m_queryString["RegisterName"] = iconv("UTF-8", "EUC-KR",$this->m_RegisterName);
				$this->m_queryString["DeliveryCoNm"] = iconv("UTF-8", "EUC-KR",$this->m_DeliveryCoNm);
			}
			$NICELog->WriteLog( DEBUG, $this->m_queryString );
		}else if (trim($this->m_ActionType) == "PYO" && trim($this->m_PayMethod) == "BILLKEY" ) {
		  
		  $this->m_uri = "/lite/billkeyProcess.jsp";
			unset($this->m_queryString);
			$this->m_queryString["CardNo"] = $this->m_CardNo;   // new
			$this->m_queryString["ExpYear"] = $this->m_ExpYear;
			$this->m_queryString["ExpMonth"] = $this->m_ExpMonth;
			$this->m_queryString["IDNo"] = $this->m_IDNo;
			$this->m_queryString["CardPw"] = $this->m_CardPw;
			$this->m_queryString["MID"] = $this->m_MID;
			$this->m_queryString["EncodeKey"] = $this->m_LicenseKey;
			$this->m_queryString["MallIP"] = $_SERVER['SERVER_NAME'];
			$this->m_queryString["actionType"] = $this->m_ActionType;
			$this->m_queryString["PayMethod"] = $this->m_PayMethod;
			
		
		// ���� ���� ����� ���
		} else if (trim($this->m_ActionType) == "PYO" && trim($this->m_PayMethod) == "OM_SUB_INS" ) {
		  
		    $this->m_uri = "/lite/payproxy/subMallSetProcess.jsp";

			unset($this->m_queryString);
			$this->m_queryString = $_POST;
			
			$this->m_queryString["EncodeKey"] = $this->m_LicenseKey;
			
		
		// ����� ��ü
		} else if (trim($this->m_ActionType) == "PYO" && trim($this->m_PayMethod) == "OM_SUB_PAY" ) {
		  
		    $this->m_uri = "/lite/payproxy/subMallIcheProcess.jsp";

			unset($this->m_queryString);
			$this->m_queryString = $_POST;
			
			$this->m_queryString["EncodeKey"] = $this->m_LicenseKey;
			
		
		// SMS
		} else if (trim($this->m_ActionType) == "PYO" && trim($this->m_PayMethod) == "SMS_REQ" ) {
		  
		    $this->m_uri = "/api/sendSmsForETAX.jsp";

			unset($this->m_queryString);
			$this->m_queryString = $_POST;
			
			$this->m_queryString["EncodeKey"] = $this->m_LicenseKey;
			
		 // ���ݿ�����,
		} else if (trim($this->m_ActionType) == "PYO" && trim($this->m_PayMethod) == "RECEIPT" ) {
		 
		  
		  $this->m_uri = "/lite/cashReceiptProcess.jsp";
			unset($this->m_queryString);
			
			$this->m_queryString["MID"] = $this->m_MID;
			$this->m_queryString["TID"] = $this->m_MID."04"."01".SetTimestamp1();
			$this->m_queryString["GoodsName"] = $this->m_GoodsName;
			$this->m_queryString["BuyerName"] = $this->m_BuyerName;
			$this->m_queryString["Amt"] = $this->m_Amt;
			$this->m_queryString["ReceiptAmt"] = $this->m_ReceiptAmt;
			$this->m_queryString["ReceiptSupplyAmt"] = $this->m_ReceiptSupplyAmt;
			$this->m_queryString["ReceiptVAT"] = $this->m_ReceiptVAT;
			$this->m_queryString["ReceiptServiceAmt"] = $this->m_ReceiptServiceAmt;
			$this->m_queryString["ReceiptType"] = $this->m_ReceiptType;
			$this->m_queryString["ReceiptTypeNo"] = $this->m_ReceiptTypeNo;
			$this->m_queryString["EncodeKey"] = $this->m_LicenseKey;
			$this->m_queryString["actionType"] = $this->m_ActionType;
			$this->m_queryString["PayMethod"] = $this->m_PayMethod;
			$this->m_queryString["CancelPwd"] = $this->m_CancelPwd;
			$this->m_queryString["CancelAmt"] = $this->m_Amt;
			$this->m_queryString["MallIP"] = $_SERVER['SERVER_NAME'];
			
		
		// ������ ���,
		} else if (trim($this->m_ActionType) == "PYO" && trim($this->m_PayMethod) != "RECEIPT" ) {
			
			if(trim($_POST["MID"]) == "") {
				$this->MakeErrorMsg( ERROR_WRONG_PARAMETER, "��û������ �Ķ���Ͱ� �߸��Ǿ����ϴ�. [MID]"); 
				return;
			} else if (trim($_POST["Amt"]) == "" ) {
				$this->MakeErrorMsg( ERROR_WRONG_PARAMETER, "��û������ �Ķ���Ͱ� �߸��Ǿ����ϴ�. [Amt]"); 
				return;
			
			}
			
			$this->m_uri = "/lite/payProcess.jsp";

			unset($this->m_queryString);

			$this->m_queryString = $_POST;
			$this->m_queryString["EncodeKey"] = $this->m_LicenseKey;
			$this->m_queryString["TID"]  = "";
			
			if($this->m_charSet =="UTF8"){
				$this->m_queryString["BuyerName"] = iconv("UTF-8", "EUC-KR",$this->m_queryString["BuyerName"]);
				$this->m_queryString["GoodsName"] = iconv("UTF-8", "EUC-KR",$this->m_queryString["GoodsName"]);
				$this->m_queryString["BuyerAddr"] = iconv("UTF-8", "EUC-KR",$this->m_queryString["BuyerAddr"]);
			}
			
		}
		
		if($this->m_debug == "DEBUG")	$NICELog->WriteArrLog($this->m_queryString);


		$httpclient = new HttpClient( $this->m_ssl );
		//connect
		if( !$httpclient->HttpConnect($NICELog) )
		{
			$NICELog->WriteLog('Server Connect Error!!' . $httpclient->getErrorMsg() );
			$resultMsg = $httpclient->getErrorMsg()."���������� �� ���� �����ϴ�.";
			if( $this->m_ssl == "true" )
			{
				$resultMsg .= "<br>������ ������ SSL����� �������� �ʽ��ϴ�. ����ó�����Ͽ��� m_ssl=false�� �����ϰ� �õ��ϼ���.";
				$this->MakeErrorMsg( ERR_SSLCONN, $resultMsg); 
			}
			else
			{
				$this->MakeErrorMsg( ERR_CONN, $resultMsg); 
			}
			
			$NICELog->CloseNiceLog("");

			return;
		}
		
		//request
		  if( !$httpclient->HttpRequest($this->m_uri, $this->m_queryString, $NICELog) ) {
			// ��û ������ ó��	
			$NICELog->WriteLog('POST Error!!' . $httpclient->getErrorMsg() );
			$this->MakeErrorMsg(ERR_NO_RESPONSE,"���� ���� ����"); 
			//NET CANCEL Start---------------------------------
			if( $httpclient->getErrorCode() == READ_TIMEOUT_ERR )
			{
				$NICELog->WriteLog("Net Cancel Start" );
		    
  			$this->m_uri = "/lite/cancelProcess.jsp";
  			unset($this->m_queryString);
  			$this->m_queryString["MID"] = substr( $this->m_TID, 0,10);
  			$this->m_queryString["TID"] = $this->m_TID;
  			$this->m_queryString["CancelAmt"] = $this->m_NetCancelAmt;
  			$this->m_queryString["CancelMsg"] = "NICE_NET_CANCEL"; 
  			$this->m_queryString["CancelPwd"] = $this->m_NetCancelPW;
			$this->m_queryString["NetCancelCode"] = "1";
  			
  			$NICELog->WriteLog($this->m_queryString["TID"]);

			if( !$httpclient->HttpConnect($NICELog) )
				{
					$NICELog->WriteLog('Server Connect Error!!' . $httpclient->getErrorMsg() );
					$resultMsg = $httpclient->getErrorMsg()."���������� �� ���� �����ϴ�.";
					$this->MakeErrorMsg( ERR_CONN, $resultMsg); 
					$NICELog->CloseNiceLog( $this->m_resultMsg );
					return;
				}
				if( !$httpclient->HttpRequest($this->m_uri, $this->m_queryString, $NICELog) && 
					($httpclient->getErrorCode() == READ_TIMEOUT_ERR) )
				{
					$NICELog->WriteLog("Net Cancel FAIL" );
					if( $this->m_ActionType == "PYO")
						$this->MakeErrorMsg( ERR_NO_RESPONSE, "���ο��� Ȯ�ο��"); 
					else if( $this->m_ActionType == "CLO")
						$this->MakeErrorMsg( ERR_NO_RESPONSE, "��ҿ��� Ȯ�ο��"); 
				}
				else
				{
					$NICELog->WriteLog("Net Cancel SUCESS" );
				}
			}
			//NET CANCEL End---------------------------------
			$this->ParseMsg($httpclient->getBody(),$NICELog);
			$NICELog->CloseNiceLog( $this->m_resultMsg );
			return;
	
		}
	
		if ( $httpclient->getStatus() == "200" ) {   
		    $this->ParseMsg($httpclient->getBody(),$NICELog);
			$NICELog->WriteLog("TID -> "."[".$this->m_ResultData['TID']."]");
			$NICELog->WriteLog($this->m_ResultData['ResultCode']."[".$this->m_ResultData['ResultMsg']."]");
			$NICELog->CloseNiceLog("");
		}else {
			$NICELog->WriteLog('SERVER CONNECT FAIL:' . $httpclient->getStatus().$httpclient->getErrorMsg().$httpclient->getHeaders() );
			$resultMsg = $httpclient->getStatus()."���������� �߻��߽��ϴ�.";
			$this->MakeErrorMsg( ERR_NO_RESPONSE, $resultMsg); 
			
			//NET CANCEL Start---------------------------------
			if( $httpclient->getStatus() != 200 )
			{
				$NICELog->WriteLog("Net Cancel Start");
		
			
				//Set Field
				$this->m_uri = "/lite/cancelProcess.jsp";
				unset($this->m_queryString);
				$this->m_queryString["MID"] = substr( $this->m_TID, 0,10);
				$this->m_queryString["TID"] = $this->m_TID;
				$this->m_queryString["CancelAmt"] = $this->m_NetCancelAmt;
				$this->m_queryString["CancelMsg"] = "NICE_NET_CANCEL";
				$this->m_queryString["CancelPwd"] = $this->m_NetCancelPW;
				$this->m_queryString["NetCancelCode"] = "1";

				if( !$httpclient->HttpConnect($NICELog) )
				{
					$NICELog->WriteLog('Server Connect Error!!' . $httpclient->getErrorMsg() );
					$resultMsg = $httpclient->getErrorMsg()."���������� �� ���� �����ϴ�.";
					$this->MakeErrorMsg( ERR_CONN, $resultMsg); 
					$NICELog->CloseNiceLog( $this->m_resultMsg );
					return;
				}
				if( !$httpclient->HttpRequest($this->m_uri, $this->m_queryString, $NICELog) )
				{
					$NICELog->WriteLog("Net Cancel FAIL" );
					if( $this->m_ActionType == "PYO")
						$this->MakeErrorMsg( ERR_NO_RESPONSE, "���ο��� Ȯ�ο��"); 
					else if( $this->m_ActionType == "CLO")
						$this->MakeErrorMsg( ERR_NO_RESPONSE, "��ҿ��� Ȯ�ο��"); 
				}
				else
				{
					$NICELog->WriteLog("Net Cancel SUCESS" );
				}
			}
			//NET CANCEL End---------------------------------
			
			$this->ParseMsg($httpclient->getBody(),$NICELog);
			$NICELog->CloseNiceLog("");
			return;
		  }
	}
	
	// ���� �޽��� ó��
	function MakeErrorMsg($err_code, $err_msg)
	{
		$this->m_ResultCode = $err_code;
		$this->m_ResultMsg = "[".$err_code."][".$err_msg."]";
		$this->m_ResultData["ResultCode"] = $err_code;
		$this->m_ResultData["ResultMsg"] =  $err_msg;
	}
	
	// ����޽��� �Ľ�
	function ParseMsg($result_string,$NICELog) {
	    $string_arr = explode("|", trim($result_string));
	    for ($num = 0; $num < count($string_arr); $num++) {
	        $parse_str = explode("=", $string_arr[$num]);
			if($this->m_charSet =="UTF8"){
				$this->m_ResultData[$parse_str[0]]  = iconv("EUC-KR", "UTF-8",$parse_str[1]);
			}else{
				$this->m_ResultData[$parse_str[0]] = $parse_str[1];
			}
	    }
	}
	
}

?>