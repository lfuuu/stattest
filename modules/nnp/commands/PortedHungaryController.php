<?php

namespace app\modules\nnp\commands;

use app\modules\nnp\models\Country;
use yii\web\NotFoundHttpException;

/**
 * <list>
 * <list_item><phone_number>19191080</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:25</update_ts></list_item>
 * <list_item><phone_number>19191081</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191082</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191083</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191084</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191085</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191086</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191087</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191088</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 * <list_item><phone_number>19191089</phone_number><equipment>001</equipment><valid_from>2016-07-07 20:00:00</valid_from><valid_until></valid_until><actual_provi
 * der>602</actual_provider><previous_provider>941</previous_provider><block_owner>941</block_owner><update_ts>2016-07-07 12:00:26</update_ts></list_item>
 */
class PortedHungaryController extends PortedController
{
    private $_operators = [
        841 => 'ACE TELECOM Telekommunikációs és Informatikai Szolgáltató Kft. ',
        702 => 'ACN Communications Hungary Kft. ',
        965 => 'Actel Távközlési Zrt. ',
        782 => 'ADERTIS Informatikai Fejlesztő és Szolgáltató Kft. ',
        812 => 'Agnátus-Pont 2004 Távközlési Szolgáltató Kereskedelmi és Ipari Kft. ',
        718 => 'Aktív-I Szolgáltató Kft. ',
        703 => 'Alfa-DH Kereskedelmi és Szolgáltató Kft. ',
        988 => 'All Info Európa Informatikai, Távközlési és Tanácsadó Kft. ',
        877 => 'ALLCOM Hungary Távközlési és Szolgáltató Kft. ',
        820 => 'AMTEL Hang és Internet Kommunikáció Magyarország Kft. ',
        850 => 'ANTENNA HUNGÁRIA Magyar Műsorszóró és Rádióhírközlési Zrt. ',
        821 => 'Arcadom Gyengeáramú és Biztonságtechnikai Kereskedelmi és Szolgáltató Kft. ',
        976 => 'ArraboNet Kereskedelmi és Szolgáltató Kft. ',
        714 => 'A.T.C. Avant Telecom Consulting AG ',
        945 => 'Atlas Telecom Távközlési Szolgáltató Kft. ',
        824 => 'Babylonphone Információ-technológiai és szolgáltató Kft. ',
        808 => 'BÁCSKÁBEL Informatikai Fejlesztő és Szolgáltató Kft. ',
        842 => 'Balmaz InterCOM Távközlési és Szolgáltató Kft. ',
        778 => 'Banktel Kommunikációs Zrt. ',
        744 => 'Baronetcomp Kft. ',
        840 => 'Berényi Kereskedelmi és Szolgáltató Kft. ',
        798 => 'Berotel Networks Kft. ',
        935 => 'Biatorbágyi Kábeltévé Szolgáltató és Kereskedelmi Kft. ',
        825 => 'BICOMIX Kereskedelmi és Szolgáltató Kft. ',
        716 => 'BORSODWEB Internet Szolgáltató Kft. ',
        722 => 'Bsystems Telekom Kft. ',
        903 => 'BT Limited Magyarországi Fióktelepe ',
        835 => 'BTM 2003 Kereskedelmi és Szolgáltató Kft. ',
        915 => 'Business Telecom Távközlési Nyrt. ',
        779 => 'Calgo Kft. ',
        759 => 'Calltivation Ltd. ',
        743 => 'CBN Telekom Kft. ',
        857 => 'Celldömölki Kábeltelevízió Műsorelosztó Kft. ',
        829 => 'Center Telecom Kft. ',
        732 => 'CG-SYSTEMS Informatikai, Kereskedelmi és Szolgáltató Kft. ',
        751 => 'Citi Telekom Limited ',
        775 => 'Citi Telekom Zrt. ',
        757 => 'CloudPrime Szolgáltató és Tanácsadó Kft. ',
        774 => 'Cogitnet Informatikai Kft. ',
        844 => 'Comtest Technikai és Ügyvitelszervezési Kft. ',
        785 => 'ConPhone Audio Conferening Kft. ',
        706 => 'Cor@Net Távközlési Zrt. ',
        954 => 'Corporate United Szolgáltató Kft. ',
        973 => 'CORRECT BUSINESS NETWORK Távközlési Szolgáltató Kft. ',
        839 => 'Corvus Telecom Informatikai és Távközlési Kft. ',
        786 => 'Cost Consulting Szolgáltató Kft. ',
        854 => 'CreaCom Média Információszolgáltató és Telekommunikációs Kft. ',
        828 => 'CSB-IMMO Ingatlanfejlesztő és Üzemeltető Kft. ',
        853 => 'DD-Cad Tervező és Szolgáltató Kft. ',
        742 => 'DELTAKON Kereskedelmi és Szolgáltató Kft. ',
        761 => 'Deltamikro Kereskedelmi és Szolgáltató Kft. ',
        978 => 'Designer Team Mérnökiroda és Szolgáltató Kft. ',
        837 => 'Dielnet Távközlési Kereskedelmi és Szolgáltató Kft . ',
        948 => 'DIGI Távközlési és Szolgáltató Kft. ',
        604 => 'DIGI Távközlési és Szolgáltató Kft. ',
        727 => 'Direct Vision Kft. ',
        921 => 'DKTV Távközlési Kereskedelmi és Szolgáltató Kft. ',
        867 => 'DRÁVANET Internet Szolgáltató Zrt. ',
        806 => 'DT Tanácsadó Kft. ',
        755 => 'Dualcomp Trade Kft. ',
        712 => 'DUAL-PLUS Szolgáltató és Kereskedelmi Kft. ',
        960 => 'DUNAKANYAR-HOLDING Pénzügyi Tanácsadó és Szolgáltató Kft. ',
        891 => 'ELEKTRONET Elektronikai és Telekommunikációs ZRt. ',
        847 => 'EnterNet 2001 Számítástechnikai Szolgáltató és Kereskedelmi Kft. ',
        796 => 'Epax Korlátolt Felelősségű Társaság ',
        949 => 'Ephone Magyarország Távközlési, Kivitelező és Szolgáltató Kft. ',
        792 => 'Ephone Schweiz GmbH. ',
        890 => 'Ephone Telekommunikációs Zrt. ',
        882 => 'EQNet Infokommunikációs Zrt. ',
        870 => 'ES Innotel Kft. ',
        814 => 'EuroCable Magyarország Kábeltelevíziós, Kereskedelmi és Szolgáltató Kft. ',
        764 => 'eVision Kereskedelmi és Szolgáltató Kft. ',
        781 => 'ExpertCom Kft. ',
        946 => 'Externet Telekommunikációs és Internet Szolgáltató Zrt. "csődeljárás alatt" ',
        780 => 'Extranet Internet Kft. ',
        748 => 'Falu-TV Szolgáltató Kft. ',
        942 => 'Faragó Sándor ',
        701 => 'FCS Group Kereskedelmi, Szolgáltató és Tanácsadó Kft. ',
        799 => 'FIX TELEKOM Kft. ',
        959 => 'Fone Távközlési, Kereskedelmi és Szolgáltató Kft. ',
        969 => 'Fonetcom Kábel-TV, Telefon és Internet Szolgáltató Kft. ',
        846 => 'FONIO-VOIP Informatikai, Kereskedelmi és Szolgáltató Kft. ',
        747 => 'Földesi és Társa 2002 Kereskedelmi és Szolgáltató Kft. ',
        612 => 'Gelka Hirtech Kereskedelmi és Szolgáltató Kft. ',
        711 => 'Gergi Háló Szolgáltató és Kereskedelmi Kft. ',
        754 => 'GigaBit Kereskedelmi és Informatikai Szolgáltató Kft. ',
        737 => 'Giganet Internet Szolgáltató Kft. ',
        602 => 'Greencom Hungary Kft. ',
        787 => 'GS-Net Kft. ',
        911 => 'GTS Hungary Távközlési Kft. ',
        909 => 'GTS Hungary Távközlési Kft. ',
        815 => 'GYŐR.NET Internetszolgáltató Kft. ',
        865 => 'HB Agora Kereskedelmi és Szolgáltató Kft. ',
        609 => 'HBCom Kábel Nonprofit Kft. ',
        738 => 'HELLO HD Kereskedelmi és Szolgáltató Kft. ',
        784 => 'HelloVoip Kft. ',
        704 => 'Hetényegyháza Kábeltelevízió Kereskedelmi és Szolgáltató KFt. ',
        819 => 'HFC Network Kivitelező és Szolgáltató Kft. ',
        735 => 'HIR-SAT 2000 Szolgáltató és Kereskedelmi Kft. ',
        729 => 'HuCom Telecom Kft. ',
        740 => 'Hungária Informatikai Kft. ',
        939 => 'HUNGAROCOM ',
        856 => 'Hungary Invest Kereskedelmi és Szolgáltató Kft. ',
        947 => 'HunNet Hungarian Networks Számítástechnikai, Oktatási és Kereskedelmi Kft. ',
        605 => 'H1 Komm Távközlési és Kereskedelmi Kft. ',
        736 => 'H1 Telekom Távközlési és Kereskedelmi Kft. ',
        944 => 'IDC ',
        746 => 'IKRON Fejlesztő és Szolgáltató Kft. ',
        985 => 'INECTEL ',
        767 => 'Inphone Adatfeldolgozó és Információs Szolgáltató Kft. ',
        884 => 'Int-air.net Számítástechnikai Szolgáltató Kft. ',
        957 => 'INTELECOM ',
        864 => 'INTELEKOM SYSTEMS Kft. ',
        817 => 'Intellihome Távközlési Szolgáltató Kft. ',
        898 => 'InterEuro Computer Kereskedelmi és Szolgáltató Kft. ',
        936 => 'Internet-X Magyarország Internet Szolgáltató és Kereskedelmi Kft. ',
        963 => 'INTERNET4U ',
        866 => 'InTV Kereskedelmi és Szolgáltató Kft. ',
        918 => 'Invitech Megoldások Zrt. ',
        932 => 'Invitel Távközlési Zrt. ',
        970 => 'Invitel Távközlési Zrt. ',
        922 => 'Invitel Távközlési Zrt. ',
        802 => 'Invitel Távközlési Zrt. ',
        912 => 'Invitel Távközlési Zrt. ',
        845 => 'Invitel Technocom Távközlési Kft. ',
        913 => 'IOCom Kereskedelmi és Szolgáltató Kft. ',
        826 => 'IP-Telekom Informatikai és Távközlési Kft. ',
        893 => 'iSAFE Informatikai Zrt. ',
        756 => 'iSave Informatika Kft. ',
        943 => 'Isis-Com Szolgáltató Kereskedelmi Kft. ',
        610 => 'i-Telecom Hungary Kft. ',
        770 => 'i3 Rendszerház Informatikai Kereskedelmi és Szolgáltató Kft. ',
        887 => 'Juhász Ervin ',
        971 => 'JUROP TELEKOM Telekommunikációs Kft. ',
        869 => '"KÁBELSAT-2000" Kábeltelevízió Építő- és Szolgáltató Kft. ',
        765 => 'KábelszatNet-2002 Műsorelosztó és Kereskedelmi Kft. ',
        834 => 'Káblex Kereskedelmi és Szolgáltató Kft. ',
        966 => 'Kalásznet Kábel TV Kft. ',
        768 => 'Kalásznet Kábel TV Kft. ',
        849 => 'KalocsaKOM Kommunikációs és Szolgáltató Kft. ',
        833 => 'Kapos-NET Szolgáltató és Kereskedelmi Kft. ',
        952 => 'Kapulan Kereskedelmi és Távközlési Kft. ',
        811 => 'KFL Informatikai és Szolgáltató Kft. ',
        992 => 'KFL Kereskedelmi és Informatikai Bt. ',
        897 => 'KFL-NETWORKING Telekommunikációs és Informatikai Kft. ',
        860 => 'KISKŐRÖSI KÁBEL-TELEVÍZIÓ Kft. ',
        762 => 'Kölcsön-Eszköz Vagyon- és Eszközkezelő Kft. ',
        726 => 'Last-Mile Telekommunikációs Kereskedelmi és Szolgáltató Kft. ',
        875 => 'LCCK Számítástechnikai és Informatikai Bt. ',
        873 => 'Leskó és Nagy Kft. ',
        904 => 'MACROgate IPsystems Magyarország Kft. ',
        934 => 'Magyar Telekom Távközlési Nyrt. ',
        906 => 'Magyar Telekom Távközlési Nyrt. ',
        956 => 'Magyar Telekom Távközlési Nyrt. ',
        928 => 'Magyar Telekom Távközlési Nyrt. ',
        916 => 'Magyar Telekom Távközlési Nyrt. ',
        886 => 'Magyar Telekommunikációs és Informatikai Kft. ',
        783 => 'MCN telecom Telekommunikációs Szolgáltató Kft. ',
        611 => 'MCNtelecom GmbH ',
        822 => 'Media Exchange Kereskedelmi és Szolgáltató Kft. ',
        710 => 'Microsemic Kft. ',
        816 => 'Microsystem Kecskemét Kereskedelmi és Szolgáltató Kft. ',
        858 => 'Micro-Telecom Telekommunikációs Kereskedelmi és Szolgáltató Kft. ',
        861 => 'MICRO-WAVE Kereskedelmi és Szolgáltató Kft. ',
        967 => 'Mikroháló Távközlési, Szolgáltató Kft. ',
        868 => 'Milenia-Systems Kereskedelmi és Szolgáltató Kft. ',
        827 => 'MIRSA EUROMOBILE Kereskedelmi és Szolgáltató Kft. ',
        788 => 'Mond21 Telekommunikációs Zrt. ',
        766 => 'MVM NET Távközlési Szolgáltató Zrt. ',
        950 => 'MyPhone Kereskedelmi és Szolgáltató Kft. ',
        772 => 'M70 Group Korlátolt Felelősségű Társaság ',
        899 => 'NARACOM Informatikai Kft. ',
        910 => 'Navigator Informatika Üzleti Szolgáltató és Kereskedelmi Zrt. ',
        793 => 'Németh Róbert ',
        900 => 'Nemzeti Média- és Hírközlési Hatóság ',
        901 => 'Nemzeti Média- és Hírközlési Hatóság ',
        795 => 'Net-Connect Communications SRL ',
        929 => 'Netfone Távközlési Szolgáltató Kft. ',
        753 => 'Netfone Telecom Távközlési és Szolgáltató Kft. ',
        777 => 'Netfone Telecom Távközlési és Szolgáltató Kft. ',
        851 => 'NetGen Infokommunikációs Kft. ',
        855 => 'Net-Portal Távközlési, Kereskedelmi és Szolgáltató Kft. ',
        872 => 'Netsurf Távközlési Szolgáltató Kft. ',
        752 => 'Net-Tv Zrt. ',
        876 => 'Network Telekom Kereskedelmi és Szolgáltató Kft. ',
        614 => 'nfon GmbH ',
        724 => 'NISZ Nemzeti Infokommunikációs Szolgáltató Zrt. ',
        984 => 'Nordtelekom Távközlési Szolgáltató Nyrt. ',
        883 => 'Novi-Com Kereskedelmi és Szolgáltató Kft. ',
        958 => 'N-System Távközlési Szolgáltató Kft. ',
        843 => '"OKSZI" Oktatói, Kereskedelmi és Szolgáltató Kft. ',
        941 => 'On Line System Informatikai és Tanácsadó Kft. ',
        961 => 'Opennetworks Kereskedelmi és Szolgáltató Kft. ',
        908 => 'Optanet Kft. ',
        863 => 'Opticon Telekommunikációs Hálózati Szolgáltató Kft. ',
        927 => 'Oros-Com Informatikai Szolgáltató Kft. ',
        831 => 'Oroszlányi Televízió Kft. ',
        836 => 'Pannon Pipics Szolgáltató és Kereskedelmi Kft. ',
        920 => 'PANTEL Holding ',
        989 => 'PARISAT Távközlési és Szolgáltató Kft. ',
        810 => 'Pázmány Kábel Szolgáltató Kft. ',
        708 => 'Pendola Invest Távközlési és Szolgáltató Kft. ',
        933 => 'Pendola TeleCom Szolgáltató Kft. ',
        805 => 'PICKUP Elektronikai Kereskedelmi és Szolgáltató Kft. ',
        794 => 'PIVo Telecom Kft. ',
        763 => 'PRIM TELEKOM Kft. ',
        741 => 'Printer-fair Számítástechnikai, Kereskedelmi és Szolgáltató Kft. ',
        848 => 'PRIVATE TEL Kereskedelmi és Szolgáltató Kft. ',
        953 => 'PR-TELECOM Távközlési, Kereskedelmi és Szolgáltató Zrt. ',
        889 => 'QuaesTel Telekommunikációs Kft. ',
        931 => 'RadioLAN Távközlési és Szolgáltató Kft. ',
        797 => 'RAYCOM-GTE Kereskedelmi és Szolgáltató Korlátolt Felelősség Társaság ',
        607 => 'Raystorm Kft ',
        608 => 'Rebell Telecommunication Zrt. ',
        773 => 'ReKo Systems Informatikai, Kereskedelmi és Szolgáltató Korlátolt Felelősségű Társaság ',
        721 => 'Rendszerinformatika Kereskedelmi és Szolgáltató Zrt. ',
        993 => 'RLAN Internet Távközlési Szolgáltató Kft. ',
        601 => 'RowanHill Communications Kft. ',
        750 => 'RubiCom Digital Kft. ',
        804 => 'RubiCom Távközlési ZRt. ',
        717 => 'R-Voice Hungary Telekommunikációs és Informatikai Kft. ',
        830 => 'Sághy-Sat Szolgáltató és Kereskedelmi Kft. ',
        894 => 'SATELIT Híradástechnikai Kft. ',
        707 => 'SKAWA Innovation Kutatás-Fejlesztési Kft. ',
        862 => 'SolarTeam Energia Szolgáltató és Kereskedelmi Kft. ',
        874 => 'Sonitar Kereskedelmi és Szolgáltató Kft. ',
        728 => 'SUPRA Kábeltelevíziós Kereskedelmi és Szolgáltató Kft. ',
        885 => 'Symlink Informatikai Szolgáltató és Tanácsadó Kft. ',
        859 => 'SysCorp Számítástechnikai és Telekommunikációs Kft. ',
        758 => 'Szabadhajdú Közművelődési Média és Rendezvényszervező Nonprofit Kft. ',
        713 => 'Szabó Elektronika Kft. ',
        818 => 'Szabolcs Kábeltelevízió Szolgáltató és Kereskedelmi Kft. ',
        879 => 'Szamosnet Informatikai és Telekommunikációs Szolgáltató Kft. ',
        923 => 'Tarr Építő, Szolgáltató és Kereskedelmi Kft. ',
        603 => 'Tarr Építő, Szolgáltató és Kereskedelmi Kft. ',
        730 => 'Techno-Tel Távközlési és Informatikai Kivitelező és Szolgáltató Kft. ',
        832 => 'Teleline 95. Kereskedelmi és Szolgáltató KFt. ',
        919 => 'Telenor Magyarország Zrt. ',
        938 => 'TELE2 ',
        734 => 'Tel2U Távközlési és Szolgáltató Kft. ',
        871 => 'TENETTEL Távközlési Szolgáltató Kft. ',
        725 => 'Tesco MBL Távközlési Zrt. ',
        771 => 'TEVE TÉVÉ Kereskedelmi és Távközlési Szolgáltató Kft. ',
        888 => 'Tiger Systems Kereskedelmi és Szolgáltató Kft. ',
        801 => 'Tiger Telekom Kereskedelmi és Szolgáltató Kft. ',
        613 => 'Tiszafüredi Kábeltévé Szövetkezet ',
        880 => '"TLT Telecom" Távközlési és Informatikai Kft. ',
        896 => 'Toldinet Internet Szolgáltató Kft. ',
        980 => 'TOMICTEL ',
        986 => 'Triotel Távközlési Kft. ',
        955 => 'TVNET ',
        937 => 'TvNetWork Telekommunikációs Szolgáltató NyRt. ',
        606 => 'UHU Systems Számítástechnikai Kft. ',
        790 => 'UPC DTH S.á.r.l. ',
        917 => 'UPC Magyarország Telekommunikációs Kft. ',
        940 => 'UPC Magyarország Telekommunikációs Kft. ',
        760 => 'UPC Magyarország Telekommunikációs Kft. ',
        705 => 'USCALL Telekommunikációs Zártkörüen Működő Részvénytársaság ',
        813 => 'Vár-Tech Szolgáltató Kft. ',
        789 => 'VCC Live Hungary Kft. ',
        983 => 'VCC Live Hungary Kft. ',
        895 => 'Verizon Magyarország Távközlési Kft. ',
        807 => 'ViDaNet Kábeltelevíziós Szolgáltató Zrt. ',
        769 => 'Vidékháló Telekommunikációs Kft. ',
        723 => 'VIP Telekom Hungary Kft. ',
        719 => 'V.I.P. Ügyfelek Tanácsadó és Szolgáltató Kft. ',
        731 => 'Virtual Communications Kft. ',
        977 => 'VIVAFONE ',
        709 => 'VNM Zrt. ',
        926 => 'Vodafone Magyarország Mobil Távközlési Zrt. ',
        803 => 'Voice-Com Szolgáltató és Kereskedelmi Kft. ',
        881 => 'VoIP NetCom Telekommunikációs Távközlési Szolgáltató Kft. ',
        852 => 'VoIP System Kft. ',
        907 => 'Voip Telekom Hungary Kereskedelmi és Szolgáltató Kft. ',
        892 => 'VoIPShop Távközlési Szolgáltató Kft. ',
        823 => 'Voxbone S.A. ',
        776 => 'Wavecom Informatikai Kereskedelmi és Szolgáltató Kft. ',
        720 => 'WebLan Magyarország Kft. ',
        930 => 'WNET Internet Távközlési Szolgáltató Kft. ',
        981 => 'Xyton Kft. ',
        809 => 'Zalaegerszegi Elektromos Karbantartó és Kereskedelmi Zrt. ',
        979 => 'Zalaszám Informatika Kft. ',
        739 => 'Zionet Informatikai, Ipari, Kereskedelmi és Szolgáltató Kft. ',
        838 => 'ZNET Telekom Zrt. ',
        745 => 'Zubor László ',
        878 => '1A Hosting Kft. ',
        715 => '1212 Telekom Gazdasági Szolgáltató Kft. ',
        902 => '3C Távközlési Kft. ',
        733 => '3LAN Kereskedelmi és Szolgáltató Kft. ',
        905 => '4VOICE Távközlési Kft. ',
        791 => '42NETMedia Szolgáltató Kft. ',
    ];

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidParamException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\db\Exception
     * @throws \LogicException
     */
    protected function readData()
    {
        $fileUrl = \Yii::getAlias('@runtime/' . $this->fileName);
        $fp = fopen($fileUrl, 'r');
        if (!$fp) {
            throw new NotFoundHttpException('Ошибка чтения файла ' . $fileUrl);
        }

        $insertValues = [];
        while (($row = fgets($fp)) !== false) {

            if (strlen($row) < 20) {
                echo 'Неправильные данные: ' . $row . PHP_EOL;
                continue;
            }

            $xml = simplexml_load_string($row);
            if (!$xml) {
                echo 'Неправильные данные: ' . $row . PHP_EOL;
                continue;
            }

            $number = (string) $xml->phone_number;
            if (!$number || !is_numeric($number)) {
                throw new \LogicException('Неправильный номер: ' . $row);
            }

            $number = Country::HUNGARY_PREFIX . $number;

            $operatorName = (string) $xml->actual_provider;
            if ($operatorName && isset($this->_operators[$operatorName]) && $this->_operators[$operatorName]) {
                $operatorName = $this->_operators[$operatorName];
            }

            $insertValues[] = [$number, $operatorName];

            if (count($insertValues) >= self::CHUNK_SIZE) {
                $this->insertValues(Country::HUNGARY, $insertValues);
            }
        }

        fclose($fp);

        if ($insertValues) {
            $this->insertValues(Country::HUNGARY, $insertValues);
        }
    }
}
