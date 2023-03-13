<?php

namespace App\Http\Controllers\Littercoin;

use App\Http\Controllers\Controller;
use App\Models\Littercoin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User\User;

class LittercoinController extends Controller
{

    /**
     * Apply middleware to all of these routes
     */
    public function __construct ()
    {
        $this->middleware('auth');
    }

    /**
     * Get an array of all of the Littercoin the User is owed
     */
    public function getUsersLittercoin ()
    {
        $userId = Auth::user()->id;

        $littercoin = Littercoin::where('user_id', $userId)->get();

        return [
            'littercoin' => $littercoin
        ];
    }

    /**
     * Get an array of the Ada and littercoin in circulation
     */
    public function getLittercoinInfo ()
    {
        $cmd = '(cd ../littercoin/;node info.mjs) 2>> ../storage/logs/littercoin.errors'; 
        $response = exec($cmd);

        return [
            $response
        ];
    }


    /**
     * Check the amount of Littercoin being minted
     * 
     * Sign the transaction
     */
    public function mintTx (Request $request)
    {
        // TODO santize inputs
        $destAddr = $request->input('destAddr');
        $changeAddr = $request->input('changeAddr');
        $utxos = $request->input('utxos');
        $strUtxos=implode(",",$utxos);

        $userId = Auth::user()->id;
        $littercoinPaid = Auth::user()->littercoin_paid;
        $littercoinEarned = Littercoin::where('user_id', $userId)->count();
        $littercoinDue = $littercoinEarned - $littercoinPaid;

        $cmd = '(cd ../littercoin/; node mint.mjs '.$littercoinDue.' '.$destAddr.' '.$changeAddr.' '.$strUtxos.') 2>> ../storage/logs/littercoin.errors'; 
        $response = exec($cmd);

        return [
            $response
        ];
        
    }



    /**
     * Submit the transaction
     *
     * Update the Littercoin amount in DB
     */
    public function submitTx (Request $request)
    {
        // TODO santize inputs
        $cborSig = $request->input('cborSig');
        $cborTx = $request->input('cborTx');

        // Check littercoin amount for this transaction
        $userId = Auth::user()->id;
        $littercoinPaid = Auth::user()->littercoin_paid;
        $littercoinEarned = Littercoin::where('user_id', $userId)->count();
        $littercoinDue = $littercoinEarned - $littercoinPaid;

        $cmd = '(cd ../littercoin/; node submit-tx.mjs '.$littercoinDue.' '.$cborSig.' '.$cborTx.') 2>> ../storage/logs/littercoin.errors'; 
        $response = exec($cmd);

        return [
            //'test' => $request->all()
            $response
        ];
    }
}
