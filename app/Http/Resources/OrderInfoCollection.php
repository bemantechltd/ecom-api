<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\OrderItemsInfoCollection;
use App\Http\Resources\PrescriptionInfoCollection;

class OrderInfoCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id'                        => $this->id,
            'customer_id'               => $this->customer_id,
            'delivery_fee'              => $this->delivery_fee,
            'discount'                  => $this->discount,
            'order_id'                  => $this->order_id,
            'paid'                      => $this->paid,
            'cancel_reason'             => $this->cancel_reason,
            'status'                    => $this->status,
            'total_amount'              => $this->total_amount,
            'total_payable'             => $this->total_payable,
            'vat_amount'                => $this->vat_amount,
            'extra_instruction'         => $this->extra_instruction,
            'choose_payment_type'       => $this->choose_payment_type,
            'order_items_info'          => OrderItemsInfoCollection::collection($this->OrderItemsInfo),
            // 'order_ship_bill_info'      => $this->OrderShipBillInfo,
            'prescription_info'         => PrescriptionInfoCollection::collection($this->PrescriptionInfo),
            'shipping_address'          => html_entity_decode($this->OrderShipBillInfo->shipping_address),
            'billing_address'           => html_entity_decode($this->OrderShipBillInfo->billing_address),
            'contact_no'                => $this->OrderShipBillInfo->contact_no,
            'email'                     => $this->OrderShipBillInfo->email,
            'delivery_timeline_info'    => $this->DeliveryTimelineInfo,
            'delivery_person_info'      => $this->DeliveryPersonInfo,
            'customer_info'             => $this->CustomerInfo,
            'created_by'                => $this->created_by,
            'updated_by'                => $this->updated_by,
            'created_at'                => date('jS, F Y H:i',strtotime($this->created_at)),
            'updated_at'                => date('jS, F Y H:i',strtotime($this->updated_at))
        ];
    }
}
