<wsdl:definitions
    xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"
    xmlns:tns="http://{{$host}}/api/avantern/shipment"
    xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    name="SoapShipmentServer"
    targetNamespace="http://{{$host}}/api/avantern/shipment">
    <wsdl:documentation />
    <wsdl:types>
        <xsd:schema targetNamespace="http://{{$host}}/api/avantern/shipment">
            <xsd:element name="saveAvanternShipment" type="tns:shipmentData" />
            <xsd:complexType name="shipmentData">
                <xsd:sequence>
                    <xsd:element name="system" type="xsd:string">
                        <xsd:annotation>
                            <xsd:documentation>
                                система-отправитель
                            </xsd:documentation>
                        </xsd:annotation>
                    </xsd:element>
                    <xsd:element name="waybill">
                        <xsd:complexType>
                            <xsd:sequence>
                                <xsd:element name="number" type="xsd:string" minOccurs="0">
                                    <xsd:annotation>
                                        <xsd:documentation>
                                            номер маршрутного листа
                                        </xsd:documentation>
                                    </xsd:annotation>
                                </xsd:element>
                                <xsd:element name="status" type="xsd:string" minOccurs="0">
                                    <xsd:annotation>
                                        <xsd:documentation>
                                            статус маршрутного листа
                                        </xsd:documentation>
                                    </xsd:annotation>
                                </xsd:element>
                                <xsd:element name="timestamp" type="xsd:string" minOccurs="0">
                                    <xsd:annotation>
                                        <xsd:documentation>
                                            TIMESTAMP(версия отправки документа)
                                        </xsd:documentation>
                                    </xsd:annotation>
                                </xsd:element>
                                <xsd:element name="date" type="xsd:string" minOccurs="0">
                                    <xsd:annotation>
                                        <xsd:documentation>
                                            дата исполнения маршрутного листа
                                        </xsd:documentation>
                                    </xsd:annotation>
                                </xsd:element>
                                <xsd:element name="time" type="xsd:string" minOccurs="0">
                                    <xsd:annotation>
                                        <xsd:documentation>
                                            время начала исполнения маршрутного листа
                                        </xsd:documentation>
                                    </xsd:annotation>
                                </xsd:element>
                                <xsd:element name="carrier" type="xsd:string" minOccurs="0">
                                    <xsd:annotation>
                                        <xsd:documentation>
                                            Название перевозчика
                                        </xsd:documentation>
                                    </xsd:annotation>
                                </xsd:element>
                                <xsd:element name="car" type="xsd:string" minOccurs="0">
                                    <xsd:annotation>
                                        <xsd:documentation>
                                            Номер машины
                                        </xsd:documentation>
                                    </xsd:annotation>
                                </xsd:element>
                                <xsd:element name="trailer" type="xsd:string" minOccurs="0">
                                    <xsd:annotation>
                                        <xsd:documentation>
                                            Номер прицепа
                                        </xsd:documentation>
                                    </xsd:annotation>
                                </xsd:element>
                                <xsd:element name="weight" type="xsd:string" minOccurs="0">
                                    <xsd:annotation>
                                        <xsd:documentation>
                                            Грузоподъемность
                                        </xsd:documentation>
                                    </xsd:annotation>
                                </xsd:element>
                                <xsd:element name="mark" type="xsd:string" minOccurs="0">
                                    <xsd:annotation>
                                        <xsd:documentation>
                                            Метка 0-Собственный/1-Наемный
                                        </xsd:documentation>
                                    </xsd:annotation>
                                </xsd:element>
                                <xsd:element name="driver" type="xsd:string" minOccurs="0">
                                    <xsd:annotation>
                                        <xsd:documentation>
                                            ФИО водителя
                                        </xsd:documentation>
                                    </xsd:annotation>
                                </xsd:element>
                                <xsd:element name="phone" type="xsd:string" minOccurs="0">
                                    <xsd:annotation>
                                        <xsd:documentation>
                                            номер телефона
                                        </xsd:documentation>
                                    </xsd:annotation>
                                </xsd:element>
                                <xsd:element name="temperature" minOccurs="0">
                                    <xsd:annotation>
                                        <xsd:documentation>
                                            температурный режим
                                        </xsd:documentation>
                                    </xsd:annotation>
                                    <xsd:complexType>
                                        <xsd:sequence>
                                            <xsd:element name="from" type="xsd:string" minOccurs="0">
                                                <xsd:annotation>
                                                    <xsd:documentation>
                                                        минимальная нормативная температура
                                                    </xsd:documentation>
                                                </xsd:annotation>
                                            </xsd:element>
                                            <xsd:element name="to" type="xsd:string" minOccurs="0">
                                                <xsd:annotation>
                                                    <xsd:documentation>
                                                        максимальная нормативная температура
                                                    </xsd:documentation>
                                                </xsd:annotation>
                                            </xsd:element>
                                        </xsd:sequence>
                                    </xsd:complexType>
                                </xsd:element>
                                <xsd:element name="stock" minOccurs="0">
                                    <xsd:annotation>
                                        <xsd:documentation>
                                            склад загрузки
                                        </xsd:documentation>
                                    </xsd:annotation>
                                    <xsd:complexType>
                                        <xsd:sequence>
                                            <xsd:element name="name" type="xsd:string" minOccurs="0">
                                                <xsd:annotation>
                                                    <xsd:documentation>
                                                        название склада загрузки
                                                    </xsd:documentation>
                                                </xsd:annotation>
                                            </xsd:element>
                                            <xsd:element name="id1c" type="xsd:string" minOccurs="0">
                                                <xsd:annotation>
                                                    <xsd:documentation>
                                                        id склада из 1С, должно передаваться только одно значение 1С или SAP
                                                    </xsd:documentation>
                                                </xsd:annotation>
                                            </xsd:element>
                                            <xsd:element name="idsap" type="xsd:string" minOccurs="0">
                                                <xsd:annotation>
                                                    <xsd:documentation>
                                                        id склада из SAP, должно передаваться только одно значение 1С или SAP
                                                    </xsd:documentation>
                                                </xsd:annotation>
                                            </xsd:element>
                                        </xsd:sequence>
                                    </xsd:complexType>
                                </xsd:element>
                                <xsd:element name="scores" minOccurs="0">
                                    <xsd:annotation>
                                        <xsd:documentation>
                                            список торговых точек
                                        </xsd:documentation>
                                    </xsd:annotation>
                                    <xsd:complexType>
                                        <xsd:sequence>
                                            <xsd:element name="score" minOccurs="0" maxOccurs="unbounded">
                                                <xsd:annotation>
                                                    <xsd:documentation>
                                                        точка
                                                    </xsd:documentation>
                                                </xsd:annotation>
                                                <xsd:complexType>
                                                    <xsd:sequence>
                                                        <xsd:element name="score" type="xsd:string" minOccurs="0">
                                                            <xsd:annotation>
                                                                <xsd:documentation>
                                                                    код торговой точки
                                                                </xsd:documentation>
                                                            </xsd:annotation>
                                                        </xsd:element>
                                                        <xsd:element name="name" type="xsd:string" minOccurs="0">
                                                            <xsd:annotation>
                                                                <xsd:documentation>
                                                                    вывеска
                                                                </xsd:documentation>
                                                            </xsd:annotation>
                                                        </xsd:element>
                                                        <xsd:element name="legal_name" type="xsd:string" minOccurs="0">
                                                            <xsd:annotation>
                                                                <xsd:documentation>
                                                                    юридическое название
                                                                </xsd:documentation>
                                                            </xsd:annotation>
                                                        </xsd:element>
                                                        <xsd:element name="adres" type="xsd:string" minOccurs="0">
                                                            <xsd:annotation>
                                                                <xsd:documentation>
                                                                    адрес торговой точки
                                                                </xsd:documentation>
                                                            </xsd:annotation>
                                                        </xsd:element>
                                                        <xsd:element name="long" type="xsd:string" minOccurs="0">
                                                            <xsd:annotation>
                                                                <xsd:documentation>
                                                                    долгота
                                                                </xsd:documentation>
                                                            </xsd:annotation>
                                                        </xsd:element>
                                                        <xsd:element name="lat" type="xsd:string" minOccurs="0">
                                                            <xsd:annotation>
                                                                <xsd:documentation>
                                                                    широта
                                                                </xsd:documentation>
                                                            </xsd:annotation>
                                                        </xsd:element>
                                                        <xsd:element name="date" type="xsd:string" minOccurs="0">
                                                            <xsd:annotation>
                                                                <xsd:documentation>
                                                                    плановая дата поставки
                                                                </xsd:documentation>
                                                            </xsd:annotation>
                                                        </xsd:element>
                                                        <xsd:element name="arrive_from" type="xsd:string" minOccurs="0">
                                                            <xsd:annotation>
                                                                <xsd:documentation>
                                                                    плановое время прибытия с
                                                                </xsd:documentation>
                                                            </xsd:annotation>
                                                        </xsd:element>
                                                        <xsd:element name="arrive_to" type="xsd:string" minOccurs="0">
                                                            <xsd:annotation>
                                                                <xsd:documentation>
                                                                    плановое время прибытия по
                                                                </xsd:documentation>
                                                            </xsd:annotation>
                                                        </xsd:element>
                                                        <xsd:element name="turn" type="xsd:string" minOccurs="0">
                                                            <xsd:annotation>
                                                                <xsd:documentation>
                                                                    номер в очереде посещения
                                                                </xsd:documentation>
                                                            </xsd:annotation>
                                                        </xsd:element>
                                                        <xsd:element name="orders" minOccurs="0">
                                                            <xsd:annotation>
                                                                <xsd:documentation>
                                                                    список заказов
                                                                </xsd:documentation>
                                                            </xsd:annotation>
                                                            <xsd:complexType>
                                                                <xsd:sequence>
                                                                    <xsd:element name="order" minOccurs="0" maxOccurs="unbounded">
                                                                        <xsd:annotation>
                                                                            <xsd:documentation>
                                                                                заказ
                                                                            </xsd:documentation>
                                                                        </xsd:annotation>
                                                                        <xsd:complexType>
                                                                            <xsd:sequence>
                                                                                <xsd:element name="order" type="xsd:string" minOccurs="0">
                                                                                    <xsd:annotation>
                                                                                        <xsd:documentation>
                                                                                            номер заказа
                                                                                        </xsd:documentation>
                                                                                    </xsd:annotation>
                                                                                </xsd:element>
                                                                                <xsd:element name="return" type="xsd:string" minOccurs="0">
                                                                                    <xsd:annotation>
                                                                                        <xsd:documentation>
                                                                                            (0 - &quot;Не возврат&quot;, 1  - &quot;Возврат&quot;  )
                                                                                        </xsd:documentation>
                                                                                    </xsd:annotation>
                                                                                </xsd:element>
                                                                                <xsd:element name="product" type="xsd:string" minOccurs="0">
                                                                                    <xsd:annotation>
                                                                                        <xsd:documentation>
                                                                                            груз
                                                                                        </xsd:documentation>
                                                                                    </xsd:annotation>
                                                                                </xsd:element>
                                                                                <xsd:element name="weight" type="xsd:string" minOccurs="0">
                                                                                    <xsd:annotation>
                                                                                        <xsd:documentation>
                                                                                            вес нетто заказа
                                                                                        </xsd:documentation>
                                                                                    </xsd:annotation>
                                                                                </xsd:element>
                                                                            </xsd:sequence>
                                                                        </xsd:complexType>
                                                                    </xsd:element>
                                                                </xsd:sequence>
                                                            </xsd:complexType>
                                                        </xsd:element>
                                                    </xsd:sequence>
                                                </xsd:complexType>
                                            </xsd:element>
                                        </xsd:sequence>
                                    </xsd:complexType>
                                </xsd:element>
                            </xsd:sequence>
                        </xsd:complexType>
                    </xsd:element>
                </xsd:sequence>
            </xsd:complexType>
        </xsd:schema>
    </wsdl:types>
    <wsdl:portType name="SoapShipmentServerPort">
        <wsdl:operation name="saveAvanternShipment">
            <wsdl:documentation>saving data</wsdl:documentation>
            <wsdl:input message="tns:saveAvanternShipmentIn"/>
        </wsdl:operation>
    </wsdl:portType>
    <wsdl:binding name="SoapShipmentServerBinding" type="tns:SoapShipmentServerPort">
        <soap:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"/>
        <wsdl:operation name="saveAvanternShipment">
            <soap:operation soapAction="http://{{$host}}/api/avantern/shipment#saveAvanternShipment"/>
            <wsdl:input>
                <soap:body use="literal"/>
            </wsdl:input>
        </wsdl:operation>
    </wsdl:binding>
    <wsdl:service name="SoapShipmentServerService">
        <wsdl:port name="SoapShipmentServerPort" binding="tns:SoapShipmentServerBinding">
            <soap:address location="http://{{$host}}/api/avantern/shipment"/>
        </wsdl:port>
    </wsdl:service>
    <wsdl:message name="saveAvanternShipmentIn">
        <wsdl:part name="parameters" element="tns:saveAvanternShipment"/>
    </wsdl:message>
</wsdl:definitions>
