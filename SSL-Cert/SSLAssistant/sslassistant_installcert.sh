#!/bin/bash
# ex30a.sh: "Colorized version of ex30.sh

Escape="\033";

BlackF="${Escape}[30m"; RedF="${Escape}[31m"; GreenF="${Escape}[32m";
YellowF="${Escape}[33m"; BlueF="${Escape}[34m"; Purplef="${Escape}[35m";
CyanF="${Escape}[36m"; WhiteF="${Escape}[37m";

BlackB="${Escape}[40m"; RedB="${Escape}[41m"; GreenB="${Escape}[42m";
YellowB="${Escape}[43m"; BlueB="${Escape}[44m"; PurpleB="${Escape}[45m";
CyanB="${Escape}[46m"; WhiteB="${Escape}[47m";

BoldOn="${Escape}[1m"; BoldOff="${Escape}[22m";
ItalicsOn="${Escape}[3m"; ItalicsOff="${Escape}[23m";
UnderlineOn="${Escape}[4m"; UnderlineOff="${Escape}[24m";
BlinkOn="${Escape}[5m"; BlinkOff="${Escape}[25m";
InvertOn="${Escape}[7m"; InvertOff="${Escape}[27m";

Reset="${Escape}[0m";

backupDateTime="%m%d%Y%H%M%S"
timestamp=`date +"$backupDateTime"`

#Change terminal to display UTF-8
LANG=en_US.UTF-8

#=========================================HEADER END=======================================================
#=========================================BRAND START=====================================================
brand="GeoTrust"
#=========================================BRAND END=======================================================



#Create SSL directory if it does not exist
symantecDir="${HOME}/${brand}/ssl"
mkdir -p ${symantecDir} >/dev/null 2>&1
if [ "$?" = 1 ]
then
    echo "Unable to create the directory '$HOME/${brand}/ssl'"
	exit 1
fi

logFile=${symantecDir}/sslassistant_${timestamp}.log
log()
{
	time=`date +"%t %x %k:%M:%S%t"`
        logentry="INFO "$time$1
	echo -e $logentry >> $logFile
}

logError()
{

	time=`date +"%t %x %k:%M:%S%t"`
	logerror="ERROR"$time$1
	echo -e $logerror >>$logFile
}
# Check to see if openssl is available
checkOpenSSLInstalled()
{
	command -v openssl &>/dev/null || { echo "Cannot locate openssl." >&2; exit 1; }
	if [ "$(id -u)" != "0" ]; then
	   echo "Please run the SSL Assitant Tool as root" 1>&2
           exit 1
         fi
}

# Check to see if curl is available
checkCurlInstalled()
{
     command -v curl &>/dev/null || { echo "Cannot locate curl.To auto download and install certificate please install curl." >&2; exit 1; }
}

# Check apache version greater than 2.2
checkApacheVersionGreaterThan_2_2()
{
   version="2.2.0"

    IFS=$'.'
    arr1=($fullVersion)
    arr2=($version)
    unset IFS

    for ((i=0;i<${#arr1[@]};++i)); do

	   if (( ${arr1[i]} == ${arr2[i]} )); then
     		apacheVersionGreaterThan_2_2="equal"
     		continue
       elif (( ${arr1[i]} < ${arr2[i]} )); then
            apacheVersionGreaterThan_2_2="false"
            break
       elif (( ${arr1[i]} > ${arr2[i]} )); then
            apacheVersionGreaterThan_2_2="true"
            break
           fi
    done
}



# Check apache version smaller than 2.4.8
checkApacheVersionGreaterThan_2_4_8()
{
    version="2.4.8"

    IFS=$'.'
    arr1=($fullVersion)
    arr2=($version)
    unset IFS

    for ((i=0;i<${#arr1[@]};++i)); do

          if (( ${arr1[i]} == ${arr2[i]} )); then
             apacheVersionGreaterThan_2_4_8="equal"
             continue
          elif (( ${arr1[i]} < ${arr2[i]} )); then
             apacheVersionGreaterThan_2_4_8="false"
             break
          elif (( ${arr1[i]} > ${arr2[i]} )); then
             apacheVersionGreaterThan_2_4_8="true"
             break
          fi
     done
}

jsonValue()
{
  KEY=$1
  num=$2
  awk -F"[,:}]" '{for(i=1;i<=NF;i++){if($i~/'$KEY'\042/){print $(i+1)}}}' | tr -d '"' | sed -n ${num}p
}



requiredPrompt()
{
	echo -e -n "$1"
	read result;
        log "Value entered for $1 $result"

	while [ -z "$result" ]; do
		echo "The value entered is invalid. Re-enter the information."
		logError "Invalid value entered for $1 $result"
                echo -e -n "$1"
		read result;
		log "Value entered for $1 $result"
    done
}

requiredFilePrompt()
{
	read -e -p "$1" result
        log "Filename entered for $1 $result"

	while [ -z "$result" ]; do
		echo "The value entered is invalid. Re-enter the information."
		logError "Invalid filename entered for $1 $result"
                read -e -p "$1" result
		log "Filename entered for $1 $result"
	done
}

requiredTwoCharPrompt()
{
	echo -e -n "$1"
	read result;
        log "Value entered for $1 $result"

	while [ "${#result}" -ne "2" ]; do
		echo "The value entered is invalid. Re-enter the information."
		logError "Invalid value entered for $1 $result"
                echo -e -n "$1"
		read result;
		log "Value entered for $1 $result"
    done
}

requiredSecretPrompt()
{
	echo -e -n "$1"
	read -s result;
	log "Value entered for Password '******'"
	echo

	while [ -z "$result" ]; do
		echo "The value entered is invalid. Re-enter the information."
		logError "Invalid value entered for Password"
                echo -e -n "$1"
		read -s result;
		echo
		log "Value entered for Password '******'"
    done
}

#Check that HTTPD version 2 is installed and mod_ssl.so is available.
checkHttpd()
{
	log  "Checking to see if httpd is available..."
        # Check to see if httpd is available
	command -v httpd &>/dev/null || { echo "Cannot locate Apache 2.0 in system path." >&2; log "Cannot locate Apache in system path."; exit 1; }

	apache=`httpd -v | grep Apache/`
	fullVersion=${apache#*/}
	fullVersion=${fullVersion% *}
	version=${fullVersion%%.*}
	
	log "Checking if httpd -v command returns valid version..."
	if [ -z "$version" ]
	then
	       echo ""httpd -v" command did not return a valid version.Please ensure you have Apache version 2.0 and above installed"
		
	else
		log  "Checking to see if httpd is version 2..."
        	if [ $version != "2" ]
		then
			echo "Apache 2.0 not found. "'('"Found version $fullVersion"')'""
			logError "Apache 2.0 not found. Found version $fullVersion"
			exit 1
		fi
	fi

	log  "Checking to see if mod_ssl is available..."
        if [ ! -e "/etc/httpd/modules/mod_ssl.so" ]
	then
		echo "Unable to find /etc/httpd/modules/mod_ssl.so. Make sure that the mod_ssl"
		echo "module is installed on this server."
		logError "Unable to find /etc/httpd/modules/mod_ssl.so. Make sure that the mod_ssl module is installed on this server."
		log "module is installed on this server."
		exit 1;
	fi
}

showEula()
{
	echo -e -n "${BoldOn}Do you accept the End User Software Licence Agreement in the eula.txt file? (y/n):${BoldOff} "
	log "Presenting End User Software Licence Agreement..."
        while [ "$accept" != "y" ]; do
		read -s -n1 accept
                log "End User Software Licence Agreement accepted."
		if [ "$accept" = "n" ]
		then
			echo n
			log "End User Software Licence Agreement declined."
			exit 0
		fi
	done
	echo y
}
getCipher(){
echo
        echo "The RSA encryption algorithm is typical for most cases, while the DSA encryption algorithm"
        echo " is a requirement for some U.S. government agencies. Only use DSA if you are sure that you need it."
        echo -e -n "${BoldOn}Please specify the cipher algorithm. ${BoldOff}\n"
	if [[ "$brand" == "Symantec" ]]
	then
		echo -e -n "${BoldOn}Choose between ${CyanF} RSA ${Reset} ${BoldOn}or ${CyanF} DSA ${Reset} ${BoldOn}or ${CyanF} ECC ${Reset}:"
	else
		echo -e -n "${BoldOn}Choose between ${CyanF} RSA ${Reset} ${BoldOn}or ${CyanF} DSA ${Reset}"
	fi

	read algo
	algo=$(echo $algo | tr '[:lower:]' '[:upper:]')
        
		if [ -z "${algo##[R][S][A]}" ]
                 then
                      cipher="RSA"
                      
                elif [ -z "${algo##[D][S][A]}" ]
                then
                      cipher="DSA"
                elif [ -z "${algo##[E][C][C]}" ] && [[ "$brand" == "Symantec" ]]
	        then
		    cipher="ECC"
  
		else
                   cipher="RSA"
                    echo
                    echo -e -n "\tInvalid selection. Defaulting to RSA"
                    echo
	fi
}
checkAlgorithmEntered(){

	count=$((count+1))
	if  echo "${AlgoOptions[@]}" | fgrep --word-regexp "$UserInput">/dev/null 2>&1; 
	then
          
	     cipher="$UserInput"
	     result=1
	    
	elif [ $count -lt 2 ]
	then
	     echo "Type the name of your preferred encryption algorithm from following value/s: "
	     for (( i=${cipherOptions}-1; i>=0; i-- ));
	     do
	              echo -e -n "${BoldOn}.${AlgoOptions[i]}${BoldOff}\n"
	     done
	     echo -n "Enter the encryption algorithm:"
    	     read UserInput
	     UserInput=$(echo $UserInput | tr '[:lower:]' '[:upper:]')
	else
   	    cipher=$defaultalgo
	    
     fi
} 

setCipher(){
	declare -i count=0
	declare -i result=0
	declare -i cipherOptions=0
	
	cipher=$(echo ${cipher} | tr '[:lower:]' '[:upper:]')
	IFS="," read -ra AlgoOptions<<<"$cipher"               
	defaultalgo="${AlgoOptions[0]}"
	cipherOptions=${#AlgoOptions[@]}

	if [ $cipherOptions -eq 1 ]
	then
	  checkAlgorithmEntered

	   if [ $result -eq 1 ]
	   then
		log "setting cipher value to $cipher" 
	   else
         	checkAlgorithmEntered
	   fi
 	else
	echo "Type the name of your preferred encryption algorithm from following value/s: "

	for (( i=${cipherOptions}-1; i>=0; i-- ));
	  do
			    
		echo -e -n "${BoldOn}.${AlgoOptions[i]}${BoldOff}\n"
	  done
		echo -n "Enter the encryption algorithm:"		
		read UserInput
		UserInput=$(echo $UserInput | tr '[:lower:]' '[:upper:]')
		checkAlgorithmEntered

		if [ $result -eq 1 ]
		then
			log "setting cipher value to $cipher" 
		else
        	 	checkAlgorithmEntered
		fi
	fi
}



#=========================================CERTIFICATE START==================================================

getCertificate()
{
 log "Reading in the Certificate..."
 certificate="-----BEGIN CERTIFICATE-----
MIIF2DCCBMCgAwIBAgIQZ7k7N91CSA5b7/J2kMjokDANBgkqhkiG9w0BAQsFADBE
MQswCQYDVQQGEwJVUzEWMBQGA1UEChMNR2VvVHJ1c3QgSW5jLjEdMBsGA1UEAxMU
R2VvVHJ1c3QgU1NMIENBIC0gRzMwHhcNMTcwNTI1MDAwMDAwWhcNMTgwNTI1MjM1
OTU5WjBiMQswCQYDVQQGEwJVUzENMAsGA1UECAwEVXRhaDEXMBUGA1UEBwwOUGxl
YXNhbnQgR3JvdmUxFDASBgNVBAoMC1NwZXJhLCBJbmMuMRUwEwYDVQQDDAxhcHAu
c3BlcmEuaW8wggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQChFZy0Cswy
ongzko1sTdxDNNsrNPytqiutLB2H6bg8kA4xKRDd+VUnh6MpaWmXTqarg7vZxjT7
LFOhd+SXSAsNkn6WIWh3i3HY19nWTYimN51xLz3arRk3SzKDpB+AlRExkjSS+eMe
d0zg0VB5b4hqmGP4fho+M5MMZOBKnriCdj8GYQfaOJRZlx2LXoNn2gnPavo/wI72
mNjda+9PNNJ6i50AB4/A6ufvyF1PBeJvknDtb65ZwxcPwZ3PQqWVQlHwYUdEEBQX
0jEFslw3Q7z707vJ59c3tIGIeJ6aYWVh8Xea+kZSjFdvHiDOJsRpI8zvxjvj+2aj
Skq0yuYORgYRAgMBAAGjggKmMIICojAXBgNVHREEEDAOggxhcHAuc3BlcmEuaW8w
CQYDVR0TBAIwADAOBgNVHQ8BAf8EBAMCBaAwKwYDVR0fBCQwIjAgoB6gHIYaaHR0
cDovL2duLnN5bWNiLmNvbS9nbi5jcmwwgZ0GA1UdIASBlTCBkjCBjwYGZ4EMAQIC
MIGEMD8GCCsGAQUFBwIBFjNodHRwczovL3d3dy5nZW90cnVzdC5jb20vcmVzb3Vy
Y2VzL3JlcG9zaXRvcnkvbGVnYWwwQQYIKwYBBQUHAgIwNQwzaHR0cHM6Ly93d3cu
Z2VvdHJ1c3QuY29tL3Jlc291cmNlcy9yZXBvc2l0b3J5L2xlZ2FsMB0GA1UdJQQW
MBQGCCsGAQUFBwMBBggrBgEFBQcDAjAfBgNVHSMEGDAWgBTSb/eW9IU/cjwwfSPa
hXibo3xafDBXBggrBgEFBQcBAQRLMEkwHwYIKwYBBQUHMAGGE2h0dHA6Ly9nbi5z
eW1jZC5jb20wJgYIKwYBBQUHMAKGGmh0dHA6Ly9nbi5zeW1jYi5jb20vZ24uY3J0
MIIBBAYKKwYBBAHWeQIEAgSB9QSB8gDwAHYA3esdK3oNT6Ygi4GtgWhwfi6OnQHV
XIiNPRHEzbbsvswAAAFcP/X3nwAABAMARzBFAiAq3UM+kDujBg7a2F8Wcb5in0px
ByzHbtgRHg2UKaVEWAIhAI9VBpqMXYuvbJ0f+D4N1mUNISguDqpWVujTsLkA8Pdc
AHYApLkJkLQYWBSHuxOizGdwCjw1mAT5G9+443fNDsgN3BAAAAFcP/X30AAABAMA
RzBFAiEAzqcSoDK9Ft92QP7SSn42sqUcB6j3Iz/8wOx6+qaymCcCIDn2GNpPac2p
wROKBYPiwobP2ZVBpOA+VwbwLhtzKXGeMA0GCSqGSIb3DQEBCwUAA4IBAQAxd0+j
6QUw6kjuWzGXodqSO4qsOOgY/TM0i6hfKMho3NTuAxnCyW9TZTw+IibeXBg11SY/
s7UHk8DDKngGgNQywX50PCsGeKyBKhDN89wZU9RdHFMwKWBdSHSemawpwB10oVdv
W8PsViTQ0yqQRx4k48yiUNvsUUKXUNVeXbDuedNDmZV2OyQjhtc3xfFH/51yC8Cv
XKmiRyhFRo5nKgjxcTxn1QRLz1mCpBRok7ryNau4Eutk34aCFyKEH3ksMcEgxNmR
31py8v+e8Nr1lxeGqiSagGeQQ0rg/9+43pQaR7Ir/+mb2BLf7ZF0nFepQYH4uvRJ
re/GAznJSyOdciA/
-----END CERTIFICATE-----
"
}

getIntermediateCerts()
{
 log "Reading in Intermediate Certificate..."
 issuerChain="-----BEGIN CERTIFICATE-----
MIIETzCCAzegAwIBAgIDAjpvMA0GCSqGSIb3DQEBCwUAMEIxCzAJBgNVBAYTAlVT
MRYwFAYDVQQKEw1HZW9UcnVzdCBJbmMuMRswGQYDVQQDExJHZW9UcnVzdCBHbG9i
YWwgQ0EwHhcNMTMxMTA1MjEzNjUwWhcNMjIwNTIwMjEzNjUwWjBEMQswCQYDVQQG
EwJVUzEWMBQGA1UEChMNR2VvVHJ1c3QgSW5jLjEdMBsGA1UEAxMUR2VvVHJ1c3Qg
U1NMIENBIC0gRzMwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDjvn4K
hqPPa209K6GXrUkkTdd3uTR5CKWeop7eRxKSPX7qGYax6E89X/fQp3eaWx8KA7UZ
U9ulIZRpY51qTJEMEEe+EfpshiW3qwRoQjgJZfAU2hme+msLq2LvjafvY3AjqK+B
89FuiGdT7BKkKXWKp/JXPaKDmJfyCn3U50NuMHhiIllZuHEnRaoPZsZVP/oyFysx
j0ag+mkUfJ2fWuLrM04QprPtd2PYw5703d95mnrU7t7dmszDt6ldzBE6B7tvl6QB
I0eVH6N3+liSxsfQvc+TGEK3fveeZerVO8rtrMVwof7UEJrwEgRErBpbeFBFV0xv
vYDLgVwts7x2oR5lAgMBAAGjggFKMIIBRjAfBgNVHSMEGDAWgBTAephojYn7qwVk
DBF9qn1luMrMTjAdBgNVHQ4EFgQU0m/3lvSFP3I8MH0j2oV4m6N8WnwwEgYDVR0T
AQH/BAgwBgEB/wIBADAOBgNVHQ8BAf8EBAMCAQYwNgYDVR0fBC8wLTAroCmgJ4Yl
aHR0cDovL2cxLnN5bWNiLmNvbS9jcmxzL2d0Z2xvYmFsLmNybDAvBggrBgEFBQcB
AQQjMCEwHwYIKwYBBQUHMAGGE2h0dHA6Ly9nMi5zeW1jYi5jb20wTAYDVR0gBEUw
QzBBBgpghkgBhvhFAQc2MDMwMQYIKwYBBQUHAgEWJWh0dHA6Ly93d3cuZ2VvdHJ1
c3QuY29tL3Jlc291cmNlcy9jcHMwKQYDVR0RBCIwIKQeMBwxGjAYBgNVBAMTEVN5
bWFudGVjUEtJLTEtNTM5MA0GCSqGSIb3DQEBCwUAA4IBAQCg1Pcs+3QLf2TxzUNq
n2JTHAJ8mJCi7k9o1CAacxI+d7NQ63K87oi+fxfqd4+DYZVPhKHLMk9sIb7SaZZ9
Y73cK6gf0BOEcP72NZWJ+aZ3sEbIu7cT9clgadZM/tKO79NgwYCA4ef7i28heUrg
3Kkbwbf7w0lZXLV3B0TUl/xJAIlvBk4BcBmsLxHA4uYPL4ZLjXvDuacu9PGsFj45
SVGeF0tPEDpbpaiSb/361gsDTUdWVxnzy2v189bPsPX1oxHSIFMTNDcFLENaY9+N
QNaFHlHpURceA1bJ8TCt55sRornQMYGbaLHZ6PPmlH7HrhMvh+3QJbBo+d4IWvMp
zNSS
-----END CERTIFICATE-----
"
 echo "${issuerChain}" > "${certChainFile}"
}

getCert()
{
  if [[ "$cipher" =~ "$defaultCipher" || -z "$cipher" ]]
  then
      getCipher
  fi

  sslDir="/etc/httpd/conf/ssl"
  if [ ! -d $sslDir ]
  then
      mkdir -p $sslDir;
  fi

  cert="${symantecDir}/${commonName}_${cipher}_certificate.crt"

  if [ -e "${cert}" ]
  then
      log echo "(Certificate installation already attempted with given commonname and algorith"
      mv ${cert} ${cert}.`date +"${backupDateTime}"`.bak
  fi

  getCertificate
  echo "${certificate}" > $cert
  echo
}

#=========================================CERTIFICATE END====================================================




installCert()
{
        getCertAndPrivateKey()
	{
              publicKey1=`openssl x509 -pubkey -noout -in $cert 2> /dev/null`
              cipher=$(echo ${cipher} | tr '[:upper:]' '[:lower:]')


		#test that publicKey1 returned is non empty
                if [ ! -n "$publicKey1" ]
		then
			echo "Public key is not found in specified certificate."
			log "Public key is not found in specified certificate."
			exit 1
		fi

		#Get the issuer name from the certificate
		issuer=`openssl x509 -in $cert -issuer -noout`
		issuer=${issuer#*CN=}
		issuer=${issuer%%/*}

		requiredFilePrompt "Enter the path for the private key that matches the certificate: "
		privateKey=$result
		echo
		if [ ! -f $privateKey ]
		then
			echo "The private key file does not exist."
			log "The private key file does not exist."
			exit 1
		fi

		requiredSecretPrompt "Enter the passphrase for the private key: "
		passphrase=$result
		echo

		
		if [[ "$cipher" == "ecc" ]]
	    then
		  publicKey2=`openssl ec -pubout -in $privateKey -passin pass:$passphrase 2> /dev/null`
		else
		  publicKey2=`openssl $cipher -pubout -in $privateKey -passin pass:$passphrase 2> /dev/null`
		fi

		if [ "$publicKey1" != "$publicKey2" ]
		then
			echo "The certificate does not contain the public key for the given private key"
			echo "or the passphrase is incorrect."
			log "The certificate does not contain the public key for the given private key"
			log "or the passphrase is incorrect."
			exit 1
		fi

		#Get the common name from privatekey as it can from the cert we cannot extract utf8 characters
		commonName=${privateKey##*/}
		commonName=${commonName%_*}
	}

	findSslConf()
	{
		sslFile="/etc/httpd/conf.d/ssl.conf"
                log "Reading into $sslFile..."

		if [ ! -e "$sslFile" ]
		then
			log "Empty file: $sslFile"
                        unset sslFile
                        return 1
		fi

		tmp=`cat $sslFile | grep "Listen 443" | wc -l`
		if [ "$tmp" -ne "1" ]
		then
			log "Listen 443 exists more than once in the $sslFile"
                        unset sslFile
			return 1
		fi

		keyFileLine=`cat $sslFile | grep -n -P "^[ \t#]*SSLCertificateKeyFile"`
		keyFileLine=${keyFileLine%:*}
		tmp=`cat /etc/httpd/conf.d/ssl.conf | grep -P "^[ \t#]*SSLCertificateKeyFile " | wc -l`
		if [ "$tmp" -ne "1" ]
		then
			log "SSLCertificateKeyFile does not exist exactly once in the $sslFile"
                        unset sslFile
			return 1
		fi

		certChainFileLine=`cat $sslFile | grep -n -P "^[ \t#]*SSLCertificateChainFile"`
		certChainFileLine=${certChainFileLine%:*}
		tmp=`cat $sslFile | grep -P "^[ \t#]*SSLCertificateChainFile" | wc -l`
		if [ "$tmp" -ne "1" ]
		then
                  if [[ "$apacheVersionGreaterThan_2_4_8" == "true" ]]  || [[ "$apacheVersionGreaterThan_2_4_8" == "equal" ]]
                  then
			           log "SSLCertificateChainFile is deprecated from 2.4.8 version"
                  else
			            log "SSLCertificateChainFile does not exist exactly once in the $sslFile"
                        unset sslFile
			            return 1
                  fi

         else 
                  if [[ "$apacheVersionGreaterThan_2_4_8" == "true" ]]  || [[ "$apacheVersionGreaterThan_2_4_8" == "equal" ]]
                  then
		                 log "SSLCertificateChainFile exist but is derprecated from 2.4.8 version "
                         unset sslFile
			             return 1
                  fi
		fi


		certFileLine=`cat $sslFile | grep -n -P "^[ \t#]*SSLCertificateFile"`
		certFileLine=${certFileLine%:*}
		tmp=`cat $sslFile | grep -P "^[ \t#]*SSLCertificateFile " | wc -l`
		if [ "$tmp" -ne "1" ]
		then
		    log "SSLCertificateFile does not exist exactly once in the $sslFile"
            unset sslFile
			return 1
		fi
	}

	#Backs up any existing intermediate certs and copies the new one into apache folder
	installIntermediateCert()
	{
		certChainFile="/etc/httpd/conf/ssl/${commonName}_intermediate.cer"
                if [ -e "${certChainFile}" ]
		then
			echo "Backing up previous intermediate certificate chain"
			log "Backing up previous intermediate certificate chain"
			mv "${certChainFile}" "${certChainFile}.`date +"${backupDateTime}"`.bak"
		fi
		getIntermediateCerts
	}

	#Backs up any existing key and copies the new key into apache folder
	installPrivateKey()
	{
                privateKeyFile="/etc/httpd/conf/ssl/${commonName}_private.key"
		if [ -e "${privateKeyFile}" ]
		then
			echo "Backing up the previous private key."
			log "Backing up the previous private key."
			mv "${privateKeyFile}" "${privateKeyFile}.`date +"${backupDateTime}"`.bak"
		fi

		log "Copying in new key..."
                cp "${privateKey}" "${privateKeyFile}"
	}

	#Backs up any existing cert and copies the new cert into apache folder
	installCertInApache()
	{
		certFile="/etc/httpd/conf/ssl/${commonName}.cer"
		if [ -e "${certFile}" ]
		then
			log "Backing up the previous certificate."
                        echo "Backing up the previous certificate."
			mv "${certFile}" "${certFile}.`date +"${backupDateTime}"`.bak"
		fi

		log "Copying in new certificate..."

                if [[ "$apacheVersionGreaterThan_2_4_8" == "true" ]]  || [[ "$apacheVersionGreaterThan_2_4_8" == "equal" ]]
                then
                     log "Apache version from 2.4.8 onwards needs cert and intermediate in one file"
                     cp $cert $certFile
                     cat $certChainFile >> $certFile
                else
                     cp $cert $certFile
                fi
	}

	updateSslConf()
	{
		echo "Backing up the $sslFile"
		log "Backing up old $sslFile"
		cp $sslFile /etc/httpd/conf.d/ssl.conf.`date +"${backupDateTime}"`.bak

		log "Updating lines for SSLCertificateKeyFile, SSLCertificateFile and SSLCertificateChainFile"
        sslConf=`cat $sslFile`
		sslConf=`echo "${sslConf}" | sed "${keyFileLine} s#.*#SSLCertificateKeyFile ${privateKeyFile////\/}#"`

        if [[ "$apacheVersionGreaterThan_2_4_8" == "true" ]]  || [[ "$apacheVersionGreaterThan_2_4_8" == "equal" ]]
        then
              log "Updating ssl.conf - Apache version from 2.4.8 onwards needs cert and intermediate in one file"
		      sslConf=`echo "${sslConf}" | sed "${certFileLine} s#.*#SSLCertificateFile ${certFile////\/}#"`
        else
		      sslConf=`echo "${sslConf}" | sed "${certFileLine} s#.*#SSLCertificateFile ${certFile////\/}#"`
		      sslConf=`echo "${sslConf}" | sed "${certChainFileLine} s#.*#SSLCertificateChainFile ${certChainFile////\/}#"`

        fi
		echo "${sslConf}" > /etc/httpd/conf.d/ssl.conf
	}

	writeSslConf()
	{
		#If the ssl.conf doesn\'t exist, we will create it. If it does, we will create a new one but
		#in the users directory. The user will have to manually configure apache.
		newSslConfFile="/etc/httpd/conf.d/ssl.conf"
		if [ -e "$newSslConfFile" -o  ! -d "/etc/httpd/conf.d" ]
		then
			log "Apache configuration not recognized."
                        echo "Apache configuration not recognized. SSL configuration "
			echo "file will be created in the user\'s home directory. Apache "
			echo "will have to be manually configured and restarted."

			newSslConfFile="$HOME/${brand}/ssl/ssl.conf"
		fi

		echo "Writing Apache SSL configuration to $newSslConfFile"
                log "Writing Apache SSL configuration to $newSslConfFile"

		echo "#Apache2 SSL configuration file" > $newSslConfFile
		echo "" >> $newSslConfFile
		echo "LoadModule ssl_module modules/mod_ssl.so" >> $newSslConfFile
		echo "Listen $port" >> $newSslConfFile
		echo "SSLPassPhraseDialog  builtin" >> $newSslConfFile
		echo "SSLSessionCache shmcb:/var/cache/mod_ssl/scache"'('"512000"')'"" >> $newSslConfFile
		echo "SSLSessionCacheTimeout  300" >> $newSslConfFile

        if [[ "$apacheVersionGreaterThan_2_2" == "false" ]]
        then
		    echo "SSLMutex default" >> $newSslConfFile
        fi

		echo "SSLRandomSeed startup file:/dev/urandom  256" >> $newSslConfFile
		echo "SSLRandomSeed connect builtin" >> $newSslConfFile
		echo "SSLCryptoDevice builtin" >> $newSslConfFile
		echo "<VirtualHost _default_:$port>" >> $newSslConfFile
		echo "  ErrorLog logs/ssl_error_log" >> $newSslConfFile
		echo "  TransferLog logs/ssl_access_log" >> $newSslConfFile
		echo "  CustomLog logs/ssl_request_log \"%t %h \\\"%r\\\" %b\"" >> $newSslConfFile
		echo "  LogLevel warn" >> $newSslConfFile
		echo "  SSLEngine on" >> $newSslConfFile
		echo "  SSLProtocol -ALL +TLSv1 +TLSv1.1 +TLSv1.2" >> $newSslConfFile
		echo "  SSLHonorCipherOrder  on" >> $newSslConfFile
		echo "  SSLCipherSuite EECDH+ECDSA+AESGCM:EECDH+aRSA+AESGCM:EECDH+ECDSA+SHA384:EECDH+ECDSA+SHA256:EECDH+aRSA+SHA384:EECDH+aRSA+SHA256:EECDH:EDH+aRSA:!aNULL:!eNULL:!LOW:!3DES:!MD5:!EXP:!PSK:!SRP:!DSS:!RC4" >> $newSslConfFile
		echo "  <IfModule mod_headers.c>" >> $newSslConfFile
        echo "        Header add Strict-Transport-Security \"max-age=15768000\" ">> $newSslConfFile
        echo "  </IfModule>" >> $newSslConfFile
		
        if [[ "$apacheVersionGreaterThan_2_4_8" == "true" ]]  || [[ "$apacheVersionGreaterThan_2_4_8" == "equal" ]]
        then
            log "Writing in new ssl.conf - Apache version from 2.4.8 onwards need cert and intermediate in one file "
		    echo "  SSLCertificateFile ${certFile}" >> $newSslConfFile
        else 
		    echo "  SSLCertificateFile ${certFile}" >> $newSslConfFile
		    echo "  SSLCertificateChainFile ${certChainFile}" >> $newSslConfFile

        fi

		echo "  SSLCertificateKeyFile ${privateKeyFile}" >> $newSslConfFile
		echo "  <Files ~ \"\\."'('"cgi|shtml|phtml|php3?"')'"$\">" >> $newSslConfFile
		echo "    SSLOptions +StdEnvVars" >> $newSslConfFile
		echo "  </Files>" >> $newSslConfFile
		echo "  <Directory "/var/www/cgi-bin">" >> $newSslConfFile
		echo "    SSLOptions +StdEnvVars" >> $newSslConfFile
		echo "  </Directory>" >> $newSslConfFile
		echo "  SetEnvIf User-Agent ".*MSIE.*" nokeepalive ssl-unclean-shutdown downgrade-1.0 force-response-1.0" >> $newSslConfFile
		echo "</VirtualHost>" >> $newSslConfFile
	}

	port=443

	checkHttpd
	getCertAndPrivateKey
    checkApacheVersionGreaterThan_2_2
    checkApacheVersionGreaterThan_2_4_8

	mkdir -p /etc/httpd/conf/ssl >/dev/null 2>&1
	if [ "$?" = 1 ]
	then
		echo "Unable to create the directory '/etc/httpd/conf/ssl'"
		log "Unable to create the directory /etc/httpd/conf/ssl"
		exit 1
	fi

	installIntermediateCert
	installPrivateKey
	installCertInApache

	findSslConf

        #SSL configuration not found for specified host
	if [ -z "${sslFile}" ]
	then
		log "SSL configuration not found for specified host, writing SSL Config."
                writeSslConf
	else
		log "SSL configuration found for specified host, updating SSL Config"
                updateSslConf
	fi

	echo
	echo "You must restart the Apache Web server to finish the certificate installation. When you "
	echo "restart Apache, you need the passphrase that you created during CSR generation."
	echo
}


clear

log "${brand} Certificate Installation Assistant Log `date`"
checkOpenSSLInstalled
showEula

echo
	if [[ "$brand" == "Symantec" ]]
	then
		echo -e "${BoldOn}${UnderlineOn}Welcome to the ${brand} Certificate Assistant Version 5.0.${UnderlineOff}${BoldOff}"
	else 
		echo -e "${BoldOn}${UnderlineOn}Welcome to the ${brand} Certificate Assistant Version 4.0.${UnderlineOff}${BoldOff}"
	fi
echo
echo
echo "Log file for this session created at $logFile"
echo

commonName="APP.SPERA.IO"
cipher="RSA"
defaultCipher="__CIPHER__"

getCert

subject=`openssl x509 -in $cert -subject -noout`

city=${subject#*L=}
city=${city%%/*}
state=${subject#*ST=}
state=${state%%/*}
country=${subject#*C=}
country=${country%%/*}

commonName=${subject#*CN=}
commonName=${commonName%%/*}
org=${subject#*O=}
org=${org%%/*}
orgunit=${subject#*OU=}
orgunit=${orgunit%%/*}

if [[ "$commonName" =~ "subject=" ]]
then
	commonName=""
fi

if [[ "$org" =~ "subject=" ]]
then
	org=""
fi

if [[ "$orgunit" =~ "subject=" || "$orgunit" =~ "verisign" || "$orgunit" =~ "www.verisign.com" ]]
then
    orgunit=""
fi

if [[ "$city" =~ "subject=" ]]
then
	city=""
fi

if [[ "$state" =~ "subject=" ]]
then
	state=""
fi

if [[ "$country" =~ "subject=" ]]
then
	country=""
fi


echo -e "${BoldOn}This tool will install the certificate with the given Distinguished Name: ${BoldOff}"
echo
echo -e "${BoldOn}Encryption Algorithm: ${BoldOff} $cipher"
echo -e "${BoldOn}Common Name: ${BoldOff}          $commonName"
echo -e "${BoldOn}Organization: ${BoldOff}         $org"
echo -e "${BoldOn}Organization Unit: ${BoldOff}    $orgunit"
echo -e "${BoldOn}City: ${BoldOff}                 $city"
echo -e "${BoldOn}State: ${BoldOff}                $state"
echo -e "${BoldOn}Country: ${BoldOff}              $country"
echo
echo
echo -e "${BoldOn}1. Continue to install certificate into Apache ${BoldOff}"
echo -e "${BoldOn}q. Quit ${BoldOff}"
echo
echo -n "Enter the task number: ";


while :
do
	read -s -n1 choice

	case $choice in
	1)
		echo 1
		log "Option chosen to install a certificate into Apache"
		installCert
		break
		;;
	q)
		echo q
		log "Option chosen to quit SSLAssistant tool"
		exit 0
	esac
done


echo "done."
