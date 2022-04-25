<?php


namespace crypto\model\local;
use crypto\model\entity\Crypto;

class CryptoProvider extends Database implements CryptoDao
{

    public function insert(Crypto... $cryptos) : bool
    {
        $valuesStr = "";

        foreach ($cryptos as $index => $item)
        {
            $valuesStr .= "(".str_replace("(","",str_replace(")","",$item)).")";

            if ($index < count($cryptos) - 1)
            {
                $valuesStr .= ",";
            }
        }

        $stmt = $this->mysqli->prepare("insert into crypto_data values ?");
        $stmt->bind_param("s",$valuesStr);
        $result = $stmt->execute();

        $stmt->close();
        return $result;
    }


    public function select(int $limit = 50, int $offset = 0) : array|false
    {
        $stmt = $this->mysqli->prepare("select * from crypto_data limit ? offset ?");
        $stmt->bind_param("ii",$limit,$offset);
        $isSuccess = $stmt->execute();

        if ($isSuccess)
        {

            $result = $stmt->get_result();
            $array = array();

            if ($result->num_rows > 0)
            {
                while (($crypto = $result->fetch_object(Crypto::class)) != null)
                {
                    $array[] = $crypto;
                }
            }

            $result->close();
            $stmt->close();
            return $array;

        }else
        {
            $stmt->close();
            return false;
        }
    }


    public function update(Crypto... $cryptos) : bool
    {
        $valuesStr = "";

        foreach ($cryptos as $index => $item)
        {
            $valuesStr .= "(".str_replace("(","",str_replace(")","",$item)).")";

            if ($index < count($cryptos) - 1)
            {
                $valuesStr .= ",";
            }
        }

        $stmt = $this->mysqli->prepare("insert into crypto_data values ? on duplicate key update 
                                                         cmc_rank = values(cmc_rank)
                                                       , num_market_pairs = values(num_market_pairs)
                                                       , circulating_supply = values(circulating_supply)
                                                       , total_supply = values(total_supply)
                                                       , max_supply = values(max_supply)
                                                       , last_updated = values(last_updated)
                                                       , date_added = values(date_added)
                                                       , tags = values(tags)
                                                       , self_reported_circulating_supply = values(self_reported_circulating_supply)
                                                       , self_reported_market_cap = values(self_reported_market_cap)
                                                       , price = values(price)
                                                       , volume_24h = values(volume_24h)
                                                       , volume_change_24h = values(volume_change_24h)
                                                       , percent_change_1h = values(percent_change_1h)
                                                       , percent_change_24h = values(percent_change_24h)
                                                       , percent_change_7d = values(percent_change_7d)
                                                       , market_cap = values(market_cap)
                                                       , market_cap_dominance = values(market_cap_dominance)
                                                       , fully_diluted_market_cap = values(fully_diluted_market_cap)
                                                       , last_updated_price = values(last_updated_price)");

        $stmt->bind_param("s",$valuesStr);
        $result = $stmt->execute();

        $stmt->close();
        return $result;
    }


    public function delete(int... $cryptoIds) : bool
    {
        $ids = implode(",",$cryptoIds);

        $stmt = $this->mysqli->prepare("delete from crypto_data where id in (?)");
        $stmt->bind_param("s",$ids);
        $result = $stmt->execute();

        $stmt->close();
        return $result;
    }

}