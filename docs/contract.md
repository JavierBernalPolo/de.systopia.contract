#**Contract Extension**


- Development Status: stable
- Used for: Extends CiviMember to support European style Contracts.
- CMS Compatibility: 4.6.
- Git URL: [click me](https://github.com/systopia/de.systopia.contract/)
- Fully Qualified Name: de.systopia.contract
- Author: Systopia.


#**Contract Extension**


##**Downloading contract files**

The membership_contract custom field in membership_general custom group can hold a contract reference. This contract reference is matched to a file located in: ```sites/default/files/civicrm/custom/contracts/{reference}.tif```

If the file does not exist, it will not be available for download and the contract reference will not be shown as a link.


##**Config**

You must create a directory or symlink in CiviCRM customFilesUploadDir "contracts". eg. ```sites/default/files/civicrm/custom/contracts```
