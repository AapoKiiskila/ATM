#ifndef LOGINDLL_H
#define LOGINDLL_H

#include <QDebug>
#include <QObject>

#include <QtNetwork>
#include <QNetworkAccessManager>
#include <QJsonDocument>

#include "loginDLL_global.h"

class LOGINDLL_EXPORT LoginDLL : public QObject
{
    Q_OBJECT
public:
    LoginDLL(QObject * parent);
    ~LoginDLL();

    void login(QString cardId, QString cardPin);

    void getCardInformation();

    void getAccountInformation();

    void lockCard();

    void logout();

    void setCardNum(const QString &newCardNum);

    void setWebToken(const QByteArray &newWebToken);

    void setUserId(const QString &newUserId);

    void setAccountId(const QString &newAccountId);

    void getCustomerInfo();

    void getWithdrawalsInfo();

    void nostotapahtuma(QString tilin_numero,QString nostot);

    void getBalance(QString);

    // Enums make it easy to handle different outcomes
    enum LoginStatus {
        InvalidCredentials,
        ConnectionError,
        Ok,
    };

private:
    QNetworkAccessManager *loginManager;
    QNetworkAccessManager *cardInfoManager;
    QNetworkAccessManager *accountInfoManager;
    QNetworkAccessManager *getManager;
    QNetworkAccessManager *postManager;
    QNetworkAccessManager *lockedManager;
    QNetworkReply *reply;
    QByteArray response_data;

    QString socket;
    QString cardNum;
    QByteArray webToken;
    QString userId;
    QString accountId;

private slots:
    void loginSlot(QNetworkReply *reply);
    void cardInfoSlot(QNetworkReply *reply);
    void accountInfoSlot(QNetworkReply *reply);
    void getCustomerSlot(QNetworkReply *reply);
    void getWithdrawalsSlot(QNetworkReply *reply);
    void getNostotapahtumaSlot(QNetworkReply *reply);
    void getBalanceSlot(QNetworkReply *reply);
    void lockCardSlot(QNetworkReply *reply);

signals:
    void loginStatus(LoginStatus);
    void customerInfo(QJsonArray);
    void withdrawalsInfo(QJsonArray);
    void accountsInfo(QJsonArray);
    void nostotapahtumaInfo(QString);
    void withdrawalDone();
    void sendBalance(QString);
};

#endif // LOGINDLL_H
