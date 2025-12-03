#!/usr/bin/env python3
# call_z_rfc_prodord_unteco.py

import os
import sys
from typing import Any, Dict, List
from dotenv import load_dotenv
from pyrfc import Connection, CommunicationError, LogonError, ABAPApplicationError, ABAPRuntimeError

# Workaround pyrfc lama yang refer 'long' (Python2 compatibility)
import builtins as _bt
if not hasattr(_bt, "long"):
    _bt.long = int

load_dotenv()

# Konfigurasi SAP – sama pola dengan script introspeksi kamu
DEFAULT_SAP = {
    "ashost": os.environ.get("SAP_ASHOST", "192.168.254.154"),
    "sysnr":  os.environ.get("SAP_SYSNR",  "01"),
    "client": os.environ.get("SAP_CLIENT", "300"),
    "lang":   os.environ.get("SAP_LANG",   "EN"),
}
SAP_USERNAME = os.environ.get("SAP_USER", "auto_email")
SAP_PASSWORD = os.environ.get("SAP_PASS", "11223344")


def main():
    # PRO yang mau dikirim
    # AUFNR biasanya 12 char numeric, kalau perlu bisa di-pad:
    aufnr_raw = "341000145366"
    iv_aufnr = aufnr_raw.rjust(12, "0")  # aman kalau ternyata kurang digit

    params = {**DEFAULT_SAP, "user": SAP_USERNAME, "passwd": SAP_PASSWORD}
    print(f"Connect SAP {params['ashost']} client {params['client']} as {SAP_USERNAME} ...")

    try:
        conn = Connection(**params)
    except (CommunicationError, LogonError) as e:
        print(f"Gagal konek SAP: {e}")
        sys.exit(2)

    print("Koneksi OK.\n")

    try:
        # Panggil RFC Z_RFC_PRODORD_UNTECO
        print(f"Memanggil Z_RFC_PRODORD_UNTECO dengan IV_AUFNR = {iv_aufnr} ...\n")

        result = conn.call(
            "Z_RFC_PRODORD_UNTECO",
            IV_AUFNR=iv_aufnr
        )

    except (ABAPApplicationError, ABAPRuntimeError) as e:
        print(f"Error ABAP saat call RFC: {e}")
        sys.exit(3)
    except CommunicationError as e:
        print(f"Error komunikasi RFC: {e}")
        sys.exit(4)
    except Exception as e:
        print(f"Error tak terduga: {e}")
        sys.exit(5)

    # --- Baca EXPORT parameter ---
    ev_subrc = result.get("EV_SUBRC")
    ev_return_msg = result.get("EV_RETURN_MSG")

    print("=== EXPORT PARAMETERS ===")
    print(f"EV_SUBRC      : {ev_subrc}")
    print(f"EV_RETURN_MSG : {ev_return_msg}")
    print()

    # --- Baca TABLE IT_RETURN (BDCMSGCOLL) ---
    it_return: List[Dict[str, Any]] = result.get("IT_RETURN", [])

    print("=== TABLE IT_RETURN (BDCMSGCOLL) ===")
    if not it_return:
        print("(IT_RETURN kosong)")
    else:
        # Tampilkan per baris
        for i, row in enumerate(it_return, start=1):
            # Field yang tadi ketemu di introspeksi:
            # TCODE, DYNAME, DYNUMB, MSGTYP, MSGSPRA, MSGID, MSGNR,
            # MSGV1, MSGV2, MSGV3, MSGV4, ENV, FLDNAME
            print(f"Row #{i}")
            print(f"  TCODE   : {row.get('TCODE')}")
            print(f"  DYNAME  : {row.get('DYNAME')}")
            print(f"  DYNUMB  : {row.get('DYNUMB')}")
            print(f"  MSGTYP  : {row.get('MSGTYP')}")
            print(f"  MSGSPRA : {row.get('MSGSPRA')}")
            print(f"  MSGID   : {row.get('MSGID')}")
            print(f"  MSGNR   : {row.get('MSGNR')}")
            print(f"  MSGV1   : {row.get('MSGV1')}")
            print(f"  MSGV2   : {row.get('MSGV2')}")
            print(f"  MSGV3   : {row.get('MSGV3')}")
            print(f"  MSGV4   : {row.get('MSGV4')}")
            print(f"  ENV     : {row.get('ENV')}")
            print(f"  FLDNAME : {row.get('FLDNAME')}")
            print("-" * 60)

    print("\nSelesai call Z_RFC_PRODORD_UNTECO.\n")


if __name__ == "__main__":
    main()