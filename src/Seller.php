<?php

namespace Dominservice\PayuMarketplace;

use Dominservice\PayuMarketplace\Api\PayU;
use Dominservice\PayuMarketplace\Api\Verification;
use Dominservice\PayuMarketplace\Exception\VerificationException;

class Seller
{
    private $associateId;
    private $associateType;
    private $associateName;
    private $associateSurname;
    private $associateCitizenship;
    private $associateIdentityNumber;
    private $associateBirthDate;
    private $associateCountryOfBirth;

    private $documentId;
    private $documentType;
    private $files = [];
    private $translationFiles = [];
    private $verificationId;
    private $sellerId;
    private $companyName;
    private $name;
    private $surname;
    private $taxId;
    private $legalForm;
    private $gusCode;
    private $registryNumber;
    private $registrationDate;
    /**
     * @var string[]
     */
    private $address;
    private $email;
    private $phone;
    private $personalIdentificationNumber;
    private $dateOfBirth;
    /**
     * @var string
     */
    private $documentNumber;
    private $issueDate;
    private $expireDate;
    private $hasDocument;
    private $swiftCode;
    private $payoutDataVerificationType;
    private $bankName;
    private $bankCountry;
    private $accountNumberFromBank;
    private $ownerName;
    private $foreign;
    private $paymentId;
    private $verified;
    private $verificationTransferId;

    /**
     * @param $id
     * @return Api\Result|false
     * @throws Exception\AuthException
     * @throws Exception\ConfigException
     * @throws Exception\NetworkException
     * @throws Exception\PayuMarketplaceException
     * @throws Exception\RequestException
     * @throws Exception\ServerErrorException
     * @throws Exception\ServerMaintenanceException
     */
    public function checkSellerIsVerified($id)
    {
        if ($data = Verification::verificationAdvice($id)) {
            return $data;
        }

        return false;
    }

    /**
     * @param $sellerId
     * @param string $tyoe
     * @return Api\Result|false
     * @throws Exception\AuthException
     * @throws Exception\ConfigException
     * @throws Exception\NetworkException
     * @throws Exception\PayuMarketplaceException
     * @throws Exception\RequestException
     * @throws Exception\ServerErrorException
     * @throws Exception\ServerMaintenanceException
     * @throws VerificationException
     */
    public function initializingVerification($sellerId, string $tyoe = PayU::TYPE_FULL)
    {
        if (!in_array($tyoe, [PayU::TYPE_PAYOUT_ACCOUNT_DATA, PayU::TYPE_FULL, PayU::TYPE_UPDATE, PayU::TYPE_REVERIFICATION, PayU::TYPE_PERSONAL_ID_TAX_ID_CHANGE])) {
            throw new VerificationException("The address for notification is missing, add it using the setNotifyUrl() method");
        }

        $seller = [
            "sellerId" => $sellerId,
            "type" => $tyoe
        ];

        if ($data = Verification::initializingVerification($seller)) {
            return $data;
        }

        return false;
    }

    /**
     * @param $typeAccount
     * @param $typeVerification
     * @return Api\Result|false
     * @throws Exception\AuthException
     * @throws Exception\ConfigException
     * @throws Exception\NetworkException
     * @throws Exception\PayuMarketplaceException
     * @throws Exception\RequestException
     * @throws Exception\ServerErrorException
     * @throws Exception\ServerMaintenanceException
     * @throws VerificationException
     */
    public function setSellerData($typeAccount = 'company', $typeVerification = PayU::TYPE_FULL)
    {
        if (empty($this->verificationId)) {
            throw new VerificationException("An empty 'verificationId' parameter must be provided to be able to query the API");
        }

        if (empty($this->sellerId)) {
            throw new VerificationException("An empty 'sellerId' parameter must be provided to be able to query the API");
        }

        $data = [
            'verificationId' => $this->verificationId,
            'sellerId' => $this->sellerId,
        ];

        if ($typeAccount === 'company') {
            if (empty($this->companyName)) {
                throw new VerificationException("An empty 'companyName' parameter must be provided to be able to query the API");
            } else {
                $data['companyName'] = $this->companyName;
            }
            if (empty($this->taxId)) {
                throw new VerificationException("An empty 'taxId' parameter must be provided to be able to query the API");
            } else {
                $data['taxId'] = $this->taxId;
            }
            if (!empty($this->gusCode)) {
                $data['gusCode'] = $this->gusCode;
            }
            if (!empty($this->registryNumber)) {
                $data['registryNumber'] = $this->registryNumber;
            }
            if (!empty($this->registrationDate)) {
                $data['registrationDate'] = $this->registrationDate;
            }
        } else {
            if (empty($this->name)) {
                throw new VerificationException("An empty 'name' parameter must be provided to be able to query the API");
            } else {
                $data['name'] = $this->name;
            }
            if (empty($this->surname)) {
                throw new VerificationException("An empty 'surname' parameter must be provided to be able to query the API");
            } else {
                $data['surname'] = $this->surname;
            }
            if ($typeVerification === PayU::TYPE_FULL && empty($this->personalIdentificationNumber)) {
                throw new VerificationException("An empty 'personalIdentificationNumber' parameter must be provided to be able to query the API");
            } elseif (!empty($this->personalIdentificationNumber)) {
                $data['personalIdentificationNumber'] = $this->personalIdentificationNumber;
            }
            if (empty($this->dateOfBirth)) {
                throw new VerificationException("An empty 'dateOfBirth' parameter must be provided to be able to query the API");
            } else {
                $data['dateOfBirth'] = $this->dateOfBirth;
            }
        }

        if (empty($this->legalForm)) {
            throw new VerificationException("An empty 'legalForm' parameter must be provided to be able to query the API");
        } elseif (!in_array($this->legalForm, ['PRIVATE_PERSON', 'SOLE_TRADER'])) {
            throw new VerificationException("legalForm value please place PRIVATE_PERSON or SOLE_TRADER.");
        } else {
            $data['legalForm'] = $this->legalForm;
        }

        if (empty($this->address)) {
            throw new VerificationException("An empty 'address' parameter must be provided to be able to query the API");
        } elseif (empty($this->address['country'])) {
            throw new VerificationException("An empty 'country' parameter in address must be provided to be able to query the API");
        } else {
            if ($typeVerification === PayU::TYPE_FULL) {
                if (empty($this->address['street'])) {
                    throw new VerificationException("An empty 'street' parameter in address must be provided to be able to query the API");
                } elseif (empty($this->address['Zipcode'])) {
                    throw new VerificationException("An empty 'Zipcode' parameter in address must be provided to be able to query the API");
                } elseif (empty($this->address['city'])) {
                    throw new VerificationException("An empty 'city' parameter in address must be provided to be able to query the API");
                }
            }
            $data['address'] = $this->address;
        }

        if (empty($this->email)) {
            throw new VerificationException("An empty 'email' parameter must be provided to be able to query the API");
        } else {
            $data['email'] = $this->email;
        }

        if (empty($this->phone)) {
            throw new VerificationException("An empty 'phone' parameter must be provided to be able to query the API");
        } else {
            $data['phone'] = $this->phone;
        }

        if ($data = Verification::setSellerData($data)) {
            return $data;
        }

        return false;
    }

    /**
     * @return Api\Result|false
     * @throws Exception\AuthException
     * @throws Exception\ConfigException
     * @throws Exception\NetworkException
     * @throws Exception\PayuMarketplaceException
     * @throws Exception\RequestException
     * @throws Exception\ServerErrorException
     * @throws Exception\ServerMaintenanceException
     * @throws VerificationException
     */
    public function setAssociates()
    {
        if (empty($this->verificationId)) {
            throw new VerificationException("An empty 'verificationId' parameter must be provided to be able to query the API");
        }

        if (empty($this->associateId)) {
            throw new VerificationException("An empty 'associateId' parameter must be provided to be able to query the API");
        }
        if (empty($this->associateType)) {
            throw new VerificationException("An empty 'associateType' parameter must be provided to be able to query the API");
        } elseif (!in_array($this->associateType, ['BENEFICIARY', 'REPRESENTATIVE'])) {
            throw new VerificationException("Associate Type is invalid, myst be between BENEFICIARY and REPRESENTATIVE");
        }

        if (empty($this->associateName)) {
            throw new VerificationException("An empty 'associateName' parameter must be provided to be able to query the API");
        }

        if (empty($this->associateSurname)) {
            throw new VerificationException("An empty 'associateSurname' parameter must be provided to be able to query the API");
        }

        if (empty($this->associateCitizenship)) {
            throw new VerificationException("An empty 'associateCitizenship' parameter must be provided to be able to query the API");
        }

        if ($this->associateCitizenship === 'PL' && empty($this->associateIdentityNumber)) {
            throw new VerificationException("An empty 'associateIdentityNumber' parameter must be provided to be able to query the API");
        } elseif ($this->associateCitizenship !== 'PL' && empty($this->associateBirthDate)) {
            throw new VerificationException("An empty 'associateBirthDate' parameter must be provided to be able to query the API");
        }

        $data = [
            'verificationId' => $this->verificationId,
            'associateId' => $this->associateId,
            'associateType' => $this->associateType,
            'associateName' => $this->associateName,
            'associateSurname' => $this->associateSurname,
            'associateCitizenship' => $this->associateCitizenship,
        ];
        if (!empty($this->associateIdentityNumber)) {
            $data['associateIdentityNumber'] = $this->associateIdentityNumber;
        }
        if (!empty($this->associateBirthDate)) {
            $data['associateBirthDate'] = $this->associateBirthDate;
        }
        if (!empty($this->associateCountryOfBirth)) {
            $data['associateCountryOfBirth'] = $this->associateCountryOfBirth;
        }

        if ($data = Verification::setAssociates($data)) {
            return $data;
        }

        return false;
    }

    /**
     * @param $filename
     * @param $filePath
     * @return Api\Result|false
     * @throws Exception\AuthException
     * @throws Exception\ConfigException
     * @throws Exception\NetworkException
     * @throws Exception\PayuMarketplaceException
     * @throws Exception\RequestException
     * @throws Exception\ServerErrorException
     * @throws Exception\ServerMaintenanceException
     * @throws VerificationException
     */
    public function setSellerFile($filename, $filePath)
    {
        if (empty($this->verificationId)) {
            throw new VerificationException("An empty 'verificationId' parameter must be provided to be able to query the API");
        }

        if (is_string($filePath)) {
            if (function_exists('curl_file_create')) {
                $filePath = curl_file_create($filePath);
            } else {
                $filePath = '@' . realpath($filePath);
            }
        }

        if (!is_object($filePath) || (is_object($filePath) && get_class($filePath) != 'CURLFile')) {
            throw new VerificationException("Invalid file is sended");
        }

        $data = [
            'verificationId' => $this->verificationId,
            'content' => $filePath,
            'filename' => $filename,
        ];

        if ($data = Verification::setFile($data)) {
            return $data;
        }

        return false;
    }

    /**
     * @return Api\Result|false
     * @throws Exception\AuthException
     * @throws Exception\ConfigException
     * @throws Exception\NetworkException
     * @throws Exception\PayuMarketplaceException
     * @throws Exception\RequestException
     * @throws Exception\ServerErrorException
     * @throws Exception\ServerMaintenanceException
     * @throws VerificationException
     */
    public function setSellerDocuments()
    {
        if (empty($this->verificationId)) {
            throw new VerificationException("An empty 'verificationId' parameter must be provided to be able to query the API");
        }

        if (empty($this->documentType) || !in_array($this->documentType, ['REGISTRY_DOCUMENT','BANK_ACCOUNT_AGREEMENT','INVOICE','UBO_STATEMENT','CIVIL_LAW_AGREEMENT','PROXY_DOCUMENT','OTHER_DOCUMENT'])) {
            throw new VerificationException("An empty 'documentType' parameter must be provided to be able to query the API and must by one of ['REGISTRY_DOCUMENT','BANK_ACCOUNT_AGREEMENT','INVOICE','UBO_STATEMENT','CIVIL_LAW_AGREEMENT','PROXY_DOCUMENT','OTHER_DOCUMENT']");
        }

        $data = [
            'verificationId' => $this->verificationId,
            'documentId' => $this->documentId,
            'type' => $this->documentType,
            'files' => $this->files,
        ];

        if (!empty($this->translationFiles)) {
            $data['translationFiles'] = $this->translationFiles;
        }

        if ($data = Verification::setSellerDocuments($data)) {
            return $data;
        }

        return false;
    }

    /**
     * @return Api\Result|false
     * @throws Exception\AuthException
     * @throws Exception\ConfigException
     * @throws Exception\NetworkException
     * @throws Exception\PayuMarketplaceException
     * @throws Exception\RequestException
     * @throws Exception\ServerErrorException
     * @throws Exception\ServerMaintenanceException
     * @throws VerificationException
     */
    public function setAssociateDocuments()
    {
        if (empty($this->verificationId)) {
            throw new VerificationException("An empty 'verificationId' parameter must be provided to be able to query the API");
        }

        if (empty($this->associateId)) {
            throw new VerificationException("An empty 'associateId' parameter must be provided to be able to query the API");
        }

        if (empty($this->documentType) || !in_array($this->documentType, ['ID_CARD', 'PASSPORT', 'DRIVING_LICENCE', 'RESIDENCE_PERMIT', 'OTHER_DOCUMENT', 'PEP_STATEMENT'])) {
            throw new VerificationException("An empty 'documentType' parameter must be provided to be able to query the API and must by one of ['ID_CARD', 'PASSPORT', 'DRIVING_LICENCE', 'RESIDENCE_PERMIT', 'OTHER_DOCUMENT', 'PEP_STATEMENT']");
        }

        if (empty($this->files)) {
            throw new VerificationException("An empty 'files' parameter must be provided to be able to query the API");
        }

        if (empty($this->documentId)) {
            throw new VerificationException("An empty 'documentId' parameter must be provided to be able to query the API");
        }

        if (empty($this->expireDate)) {
            throw new VerificationException("An empty 'expireDate' parameter must be provided to be able to query the API");
        }

        $data = [
            'verificationId' => $this->verificationId,
            'associateId' => $this->associateId,
            'type' => $this->documentType,
            'files' => $this->files,
            'documentId' => $this->documentId,
            'expireDate' => $this->expireDate,
        ];

        if (!empty($this->documentNumber)) {
            $data['documentNumber'] = $this->documentNumber;
        }

        if (!empty($this->issueDate)) {
            $data['issueDate'] = $this->issueDate;
        }

        if (!empty($this->translationFiles)) {
            $data['translationFiles'] = $this->translationFiles;
        }

        if ($data = Verification::setSellerDocuments($data)) {
            return $data;
        }

        return false;
    }

    public function setPayoutDetails($tyoeVerification = PayU::TYPE_FULL)
    {
        if (empty($this->verificationId)) {
            throw new VerificationException("An empty 'verificationId' parameter must be provided to be able to query the API");
        }

        if (empty($this->bankDataId)) {
            throw new VerificationException("An empty 'bankDataId' parameter must be provided to be able to query the API");
        }

        if (empty($this->accountNumberRequested)) {
            throw new VerificationException("An empty 'accountNumberRequested' parameter must be provided to be able to query the API");
        }

        if (empty($this->payoutDataVerificationType)) {
            throw new VerificationException("An empty 'payoutDataVerificationType' parameter must be provided to be able to query the API");
        }

        if (empty($this->foreign)) {
            $this->foreign = 'false';
        }

        if (empty($this->verified)) {
            $this->verified = 'false';
        }

        $data = [
            'bankDataId' => $this->bankDataId,
            'verificationId' => $this->verificationId,
            'accountNumberRequested' => $this->accountNumberRequested,
            'payoutDataVerificationType' => $this->payoutDataVerificationType,
            'foreign' => $this->foreign,
            'expireDate' => $this->expireDate,
            'verified' => $this->verified,
        ];

        if (!empty($this->hasDocument)) {
            $data['hasDocument'] = $this->hasDocument;
        }

        if (!empty($this->issueDate)) {
            $data['issueDate'] = $this->issueDate;
        }

        if (!empty($this->translationFiles)) {
            $data['translationFiles'] = $this->translationFiles;
        }

        if ($this->foreign || !empty($this->accountNumberFromBank)) {
            $data['statementData']['accountNumberFromBank'] = $this->accountNumberFromBank;
        }
        if (!empty($this->ownerName)) {
            $data['statementData']['ownerName'] = $this->ownerName;
        }
        if (!empty($this->address)) {
            $data['statementData']['address'] = $this->address;
        }

        if ($this->foreign || !empty($this->swiftCode)) {
            $data['swiftCode'] = $this->swiftCode;
        }

        if ($this->foreign || !empty($this->bankName)) {
            $data['bankName'] = $this->bankName;
        }

        if ($this->foreign || !empty($this->bankCountry)) {
            $data['bankCountry'] = $this->bankCountry;
        }

        if (!empty($this->paymentId )) {
            $data['paymentId '] = $this->paymentId ;
        }
        if (!empty($this->verificationTransferId )) {
            $data['verificationTransferId '] = $this->verificationTransferId ;
        }

        if ($data = Verification::setPayoutDetails($data)) {
            return $data;
        }

        return false;
    }



    public function __call($m, $a = null)
    {
        if (count($a) === 1) {
            $a = $a[0];
        }

        $method = lcfirst(preg_replace('#(^get|^set)#', '', $m));
        if (preg_match('#^get#', $m)) {
            return isset($this->$method) ? $this->$method : null;
        } elseif (preg_match('#^set#', $m)) {
            if (in_array($method, ['translationFiles', 'files'])) {
                $this->$method[] = $a;
            } elseif (in_array($method, ['hasDocument', 'verified', 'foreign'])) {
                $this->$method[] = (bool)$a ? 'true' : 'false';
            } elseif (in_array($method, ['address', 'statementAddress'])) {
                if ($method === 'address' && empty($a[0])) {
                    throw new VerificationException("Country is Required in address");
                }

                $this->address = [];

                if ($method === 'address' || !empty($a[0])) {
                    $this->address['country'] = $a[0];
                }

                if (!empty($a[1])) {
                    $this->address['street'] = $a[1];
                }

                if (!empty($a[2])) {
                    $this->address['Zipcode'] = $a[2];
                }

                if (!empty($a[3])) {
                    $this->address['city'] = $a[3];
                }

                if ($method === 'address' && isset($a[4])) {
                    $this->address['isAccountCloned'] = (bool)$a[4] ? 'true' : 'false';
                }
            } else {
                $this->$method = $a;
            }
            return $this;
        }

        throw new VerificationException("Call to undefined method Dominservice\PayuMarketplace\Seller::{$m}() ");
    }

}
